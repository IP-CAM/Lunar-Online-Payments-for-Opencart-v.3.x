<?php

require_once(DIR_SYSTEM . 'library/Lunar/vendor/autoload.php');
require_once(DIR_SYSTEM . 'library/Lunar/helper/LunarHelper.php');

use Lunar\Lunar as ApiClient;
use Lunar\Exception\ApiException;

/**
 * 
 */
class ControllerExtensionPaymentLunarTransaction extends \Controller
{
    const MODEL_PATH = 'extension/payment/lunar_transaction';

    private ApiClient $lunarApiClient;

    /** @var int|string|null */
    private $orderId;

    private string $storeId;
    private $paymentMethod;
    private $logger;
    private bool $testMode;

    public function index() {}

    public function __construct($registry) {
		parent::__construct($registry);

        $this->load->language(LunarHelper::LUNAR_GENERAL_PATH);
        $this->load->model(self::MODEL_PATH);
        $this->load->model('checkout/order');
        $this->load->model('setting/setting');
        
        $this->orderId = $this->request->get['order_id'];
        
        $this->order = $this->model_checkout_order->getOrder($this->orderId);

        if (!$this->order) {
            $this->writeLog('No order found with ID: ' . $this->orderId);
            $this->session->data['error_warning'] = $this->language->get('error_no_order_found');
        }

        if (!in_array($this->order['payment_code'], LunarHelper::LUNAR_METHODS)) {
            return;
        }

        $this->storeId = $this->order['store_id'];

        $this->paymentMethod = $this->order['payment_code'];

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
        $exceptionMessage = null;

        $lastTransaction = $this->model_extension_payment_lunar_transaction->getLastTransaction($this->orderId);

        if (!$lastTransaction) {
            return ['error' => $this->language->get('error_empty_transaction_result')];
        }

        $orderStatusId = $this->order['order_status_id'];
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

            switch ($orderStatusId) {
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
            $exceptionMessage = $e->getMessage();
        } catch (\Exception $e) {
            $this->writeLog('General Exception: ' . $e->getMessage());
            $exceptionMessage = $e->getMessage();
        }
        
        if ($exceptionMessage) {
            return ['error' => $exceptionMessage];
        }

        if (isset($response["{$actionType}State"]) && 'completed' != $response["{$actionType}State"]) {
            $errorMessage = $this->language->get('error_message');
            if ($response['declinedReason'] ?? null) {
                $errorMessage = $response['declinedReason']['error'] ?? json_encode($errorMessage);
                $this->writeLog('Declined transaction: ' . $errorMessage);
            }
            return ['error' => $errorMessage];
        }

        $this->writeLog('API response: ' . json_encode($response));

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
        $this->model_extension_payment_lunar_transaction->updateOrder($dataForDB, $orderStatusId);

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
