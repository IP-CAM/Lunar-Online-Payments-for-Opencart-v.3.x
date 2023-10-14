<?php

require_once(DIR_SYSTEM . 'library/Lunar/vendor/autoload.php');


use Lunar\Lunar as ApiClient;
use Lunar\Exception\ApiException;

/**
 * 
 */
class ControllerExtensionPaymentLunarTransaction extends \Controller
{
    const MODEL_PATH = 'extension/payment/lunar_transaction';
    const LUNAR_METHODS = [
        'lunar_card',
        'lunar_mobilepay',
    ];

    /** @var int|string|null */
    private $orderId;

    private $logger;
    private bool $testMode;
    private string $storeId;

    public function index() {}

    public function __construct($registry) {
		parent::__construct($registry);

        $this->load->language('extension/payment/lunar');
        $this->load->model(self::MODEL_PATH);
        $this->load->model('checkout/order');
        $this->load->model('setting/setting');
        
        $this->orderId = $this->request->get['order_id'];
        
        $this->order = $this->model_checkout_order->getOrder($this->orderId);

        if (!$this->order) {
            $this->writeLog('No order found with ID: ' . $this->orderId);
            $this->session->data['error_warning'] = $this->language->get('error_no_order_found');
        }

        if (!in_array($this->order['payment_code'], self::LUNAR_METHODS)) {
            return;
        }

        $this->storeId = $this->order['store_id'];

        $this->logger = new Log($this->order['payment_code'] . '.log');

        $this->testMode = 'test' == $this->getSettingValue('api_mode');
        if ($this->testMode) {
            $privateKey =  $this->getSettingValue('app_key_test');
        } else {
            $privateKey = $this->getSettingValue('app_key_live');
        }

        /** API Client instance */
        $this->lunarApiClient = new ApiClient($privateKey, null, $this->testMode);
	}

    /*******************************************************************************
     ********************* LOGIC USED IN ADMIN BACKEND  ****************************
     *******************************************************************************/

    /**
     * (here is the place the event 'catalog/controller/api/order/history/after' can reach this function)
     */
    public function makeTransactionOnOrderStatusChange(&$route, &$args)
    {
        $resultArray = $this->execute();
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($resultArray));
    }

    /**
     * Execute the transaction.
     */
    public function execute()
    {
        $lastTransaction = $this->model_extension_payment_lunar_transaction->getLastTransaction($this->orderId);

        if (!$lastTransaction) {
            return ['error' => $this->language->get('error_empty_transaction_result')];
        }

        $paymentIntentId = $lastTransaction['transaction_id'];
        $currency = $lastTransaction['transaction_currency'];
        $amount = $lastTransaction['transaction_amount'];

        $this->writeLog('************* admin ****************');
        $this->writeLog('Admin transaction for order: ' . $this->orderId . ' (' . $amount . ' ' . $currency . ')');
        $this->writeLog('Transaction Reference: ' . $paymentIntentId);
        
        try {
            $fetchedTransaction = $this->lunarApiClient->payments()->fetch($paymentIntentId);

            if (!$fetchedTransaction) {
                $this->writeLog('Unable to Fetch transaction for intent ID: ' . $paymentIntentId);
                return ['error' => $this->language->get('error_empty_transaction_result')];
            }

            if ($fetchedTransaction['amount']['currency'] != $lastTransaction['transaction_currency']) {
                $this->writeLog('Error: Capture currency (' . $currency . ') not equal to Transaction currency (' . $fetchedTransaction['transaction']['currency'] . '). Transaction aborted!');
                return ['error' => $this->language->get('error_transaction_currency')];
            }

            $data = [
                'amount' => [
                    'currency' => $currency,
                    'decimal' => $amount,
                ]
            ];

            $actionType = '';
            $response = [];


            switch ($this->order['order_status_id']) {
                case $this->getSettingValue('capture_status_id'):
                    $actionType = 'capture';
                    $response = $this->lunarApiClient->payments()->capture($paymentIntentId, $data);
                    break;
    
                case $this->getSettingValue('refund_status_id'):
                    $actionType = 'refund';
                    $response = $this->lunarApiClient->payments()->refund($paymentIntentId, $data);
                    break;
    
                case $this->getSettingValue('cancel_status_id'):
                    $actionType = 'cancel';
                    $response = $this->lunarApiClient->payments()->cancel($paymentIntentId, $data);
                    break;
            }
        } catch (ApiException $e) {
            $this->writeLog('API Exception: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }

        if (isset($response["{$actionType}State"]) && 'completed' != $response["{$actionType}State"]) {
            $errorMessage = $this->language->get('error_message');
            if ($response['declinedReason'] ?? null) {
                $errorMessage = $response['declinedReason'];
                $this->writeLog('Declined transaction: ' . $errorMessage);
            }
            return ['error' => $errorMessage];
        }

        $dataForDB = [
            'order_id'             => $this->orderId,
            'transaction_id'       => $paymentIntentId,
            'transaction_type'     => $actionType,
            'transaction_currency' => $currency,
            'order_amount'         => $lastTransaction['order_amount'],
            'transaction_amount'   => $amount,
            'history'              => '0',
        ];

        $this->model_extension_payment_lunar_transaction->addTransaction($dataForDB);
        $this->model_extension_payment_lunar_transaction->updateOrder($dataForDB, $this->order['order_status_id']);

        return ['success' => sprintf($this->language->get("success_transaction_{$actionType}"), $amount) . ' ' . $currency];
    }

    /**
     * @return mixed
     */
    private function getSettingValue($key)
    {
        return $this->model_setting_setting->getSettingValue('payment_' . $this->paymentMethod . '_' . $key, $this->storeId);
    }
    
    /**
     * 
     */
    private function writeLog($logMessage)
    {
        if ($this->getSettingValue('logging')) {
            $this->logger->write($logMessage);
        }
    }
}
