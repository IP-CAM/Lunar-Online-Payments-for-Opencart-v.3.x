<?php

require_once(DIR_SYSTEM . 'library/Lunar/vendor/autoload.php');
require_once(DIR_SYSTEM . 'library/Lunar/helper/LunarHelper.php');


use Lunar\Lunar as ApiClient;
use Lunar\Exception\ApiException;

/**
 * 
 */
abstract class AbstractLunarFrontController extends \Controller
{
    const REMOTE_URL = 'https://pay.lunar.money/?id=';
    const TEST_REMOTE_URL = 'https://hosted-checkout-git-develop-lunar-app.vercel.app/?id=';

    protected string $paymentMethodCode = '';
    protected string $paymentMethodConfigCode = '';
    protected string $extensionPath = '';

    protected ApiClient $lunarApiClient;

    protected string $error = '';
    protected bool $testMode = false;
    protected bool $isInstantMode = false;
    protected array $args = [];
    protected string $publicKey = '';
    protected ?string $paymentIntentId = null;
    protected ?array $dbTransaction = null;

    /** @var int|string|null */
    protected $orderId;

    public function __construct($registry) {
		parent::__construct($registry);

        $this->extensionPath = 'extension/payment/' . LunarHelper::LUNAR_METHODS[$this->paymentMethodCode];

        $this->load->language(LunarHelper::LUNAR_GENERAL_PATH);
        $this->load->model($this->extensionPath);
        $this->load->model('extension/payment/lunar_transaction');
        $this->load->model('checkout/order');

        $this->logger = new Log('lunar_' . $this->paymentMethodCode . '.log');

        $this->orderId = $this->request->get['order_id']
                            ?? $this->session->data['order_id']
                            ?? null;

        $this->order = $this->model_checkout_order->getOrder($this->orderId);

        if (!$this->order) {
            $this->writeLog('No order found: ID ' . $this->orderId, true);
            $this->error = $this->language->get('error_no_order_found');
        }

        $this->isInstantMode = 'instant' == $this->getConfigValue('capture_mode');

        $this->testMode = (bool) ($this->request->cookie['lunar_testmode'] ?? 0); // possible values: 1 & 0

        $this->publicKey = $this->getConfigValue('public_key');

        /** API Client instance */
        $this->lunarApiClient = new ApiClient($this->getConfigValue('app_key'), null, $this->testMode);
	}

    /**
     * 
     */
    public function index()
    {
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['methodCode'] = $this->paymentMethodCode;
        $data['paymentRedirectUrl'] = $this->url->link($this->extensionPath . '/redirect');
        $data['error'] = $this->error;

        return $this->load->view(LunarHelper::LUNAR_GENERAL_PATH, $data);
    }

    /**
     * 
     */
    public function redirect()
    {
        $this->setArgs();

        $this->paymentIntentId = $this->getPaymentIntentFromTransaction($this->dbTransaction);
        if (!$this->paymentIntentId) {
            try {
                $this->paymentIntentId = $this->lunarApiClient->payments()->create($this->args);
            } catch (ApiException $e) {
                $this->writeLog('Exception creating payment intent: ' . $e->getMessage());
                $data['error'] = "An exception occurred! <br> Please refresh and try again or contact system administrator.";
                return $this->sendJsonResponse($data);
            }
        }

        if (empty($this->paymentIntentId)) {
            $data['error'] = "An error occurred creating payment intent for order. <br> Please try again or contact system administrator.";
            return $this->sendJsonResponse($data);
        }

        $data = [
            'order_id' => $this->orderId,
            'transaction_id' => $this->paymentIntentId,
            'transaction_currency' => $this->order['currency_code'],
            'order_amount' => $this->order['total'],
        ];

        $this->savePaymentIntentOnTransaction($data);

        $redirectUrl = self::REMOTE_URL . $this->paymentIntentId;
        if($this->testMode) {
            $redirectUrl = self::TEST_REMOTE_URL . $this->paymentIntentId;
        }

        $data['redirect'] = $redirectUrl;

        $this->sendJsonResponse($data);
    }

    /**
     * 
     */
    public function callback()
    {
        $this->load->language(LunarHelper::LUNAR_GENERAL_PATH);

        $this->paymentIntentId = $this->getPaymentIntentFromTransaction();

        if (! $this->paymentIntentId) {
            $this->writeLog('No payment intent id found for order: ID ' . $this->orderId, true);
            $this->session->data['error'] = $this->language->get('error_no_transaction_found');
            return $this->response->redirect($this->url->link('checkout/checkout'));
        }

        $this->writeLog('************');
        $this->writeLog('Lunar callback. Transaction reference: ' . $this->paymentIntentId);
        
        try {

            $apiResponse = $this->lunarApiClient->payments()->fetch($this->paymentIntentId);        

            if (! $this->parseApiTransactionResponse($apiResponse)) {
                $errorResponse = $this->getResponseError($apiResponse);
                $this->writeLog('Api error response: ' . $errorResponse);
                $this->session->data['error'] = $errorResponse;
                $this->response->redirect($this->url->link('checkout/checkout'));
            }

            $params = [
                'amount' => [
                    'currency' => $this->order['currency_code'],
                    'decimal' => (string) $this->order['total'],
                ]
            ];
            
            $this->writeLog(json_encode(['Callback payment params' => $params]));

            $transactionType = 'authorize';
            $newOrderStatus = $this->getConfigValue('authorize_status_id');
            $successMessage = $this->language->get('success_message_authorized');
            
            if ($this->isInstantMode) {

                $captureResponse = $this->lunarApiClient->payments()->capture($this->paymentIntentId, $params);

                if ('completed' != $captureResponse['captureState']) {
                    $errorMessage = $this->language->get('error_transaction_not_captured');

                    if ($captureResponse['declinedReason'] ?? null) {
                        $errorMessage = $captureResponse['declinedReason']['error'] ?? json_encode($captureResponse);
                        $this->writeLog('Declined Lunar transaction: ' . $errorMessage, true);
                    }
                    $this->session->data['error'] = $errorMessage;
                    $this->response->redirect($this->url->link('checkout/checkout'));
                }

                $transactionType = 'capture';
                $newOrderStatus = $this->getConfigValue('capture_status_id');
                $successMessage = $this->language->get('success_message_captured');
            }

        } catch (ApiException $e) {
            $this->writeLog('Frontend Lunar API Exception: ' . $e->getMessage(), true);
            $this->session->data['error'] = $this->language->get('error_transaction_exception');
            return $this->response->redirect($this->url->link('checkout/checkout'));
        }

        $currency = $apiResponse['amount']['currency'];
        $amount = $apiResponse['amount']['decimal'];
            
        $transactionData = [
            'order_id' => $this->orderId,
            'transaction_id' => $this->paymentIntentId,
            'transaction_type' => $transactionType,
            'transaction_currency' => $currency,
            'order_amount' => $this->order['total'],
            'transaction_amount' => $amount,
        ];

        $comment = 'Transaction ref: ' . $this->paymentIntentId
                    . "\r\n" . ucfirst($transactionType) . 'd amount: ' . $this->currency->format($amount, $currency);
        $this->model_checkout_order->addOrderHistory($this->orderId, $newOrderStatus, $comment, $notify = true);

        $this->updateInitTransaction($transactionData);

        $this->session->data['success'] = $successMessage;
        $this->response->redirect($this->url->link('checkout/success'));
    }

    /**
     * 
     */
    private function setArgs()
    {
        $order = $this->order;

        $this->args = [
            'integration' => [
                'key' => $this->publicKey,
                'name' => $this->getConfigValue('shop_title'),
                'logo' => $this->getConfigValue('logo_url'),
            ],
            'amount' => [
                'currency' => $order['currency_code'],
                'decimal' => (string) $order['total'],
            ],
            'custom' => [
                'orderId' => $order['order_id'],
                'products' => $this->getFormattedProducts(),
                'customer' => [
                    'name' => $order['payment_firstname'] . ' ' . $order['payment_lastname'],
                    'email' => $order['email'],
                    'phoneNo' => $order['telephone'],
                    'address' => $order['payment_address_1'] . ', '
                                    . $order['payment_address_2'] != '' ? $order['payment_address_2'] . ', ' : ''
                                    . $order['payment_city'] . ', ' . $order['payment_zone'] . ', '
                                    . $order['payment_country'] . ' - ' . $order['payment_postcode'],
                    'ip' => $order['ip'],
                ],
                'platform' => [
                    'name' => 'Opencart',
                    'version' => VERSION,
                ],
                'lunarPluginVersion' => LunarHelper::pluginVersion(),
            ],
            'preferredPaymentMethod' => $this->paymentMethodCode,
            'redirectUrl' => $this->url->link($this->extensionPath . '/callback&order_id=' . $order['order_id']),
        ];

        if ($this->getConfigValue('configuration_id')) {
            $this->args['mobilePayConfiguration'] = [
                'configurationID' => $this->getConfigValue('configuration_id'),
                'logo' => $this->getConfigValue('logo_url'),
            ];
        }

        if ($this->testMode) {
            $this->args['test'] = $this->getTestObject();
        }
    }

    /**
     * 
     */
    private function sendJsonResponse($data)
    {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

    /**
     * 
     */
    private function savePaymentIntentOnTransaction($data)
    {
        $this->model_extension_payment_lunar_transaction->savePaymentIntentOnTransaction($data);
    }

    /**
     * 
     */
    private function getPaymentIntentFromTransaction()
    {
        $initTransaction = $this->model_extension_payment_lunar_transaction->getLastTransaction($this->orderId);
        return ($initTransaction && $initTransaction['transaction_type'] == 'INIT')
                ? $initTransaction['transaction_id']
                : null;
    }

    /**
     * 
     */
    private function updateInitTransaction($data)
    {
        $this->model_extension_payment_lunar_transaction->updateInitTransaction($data);
    }

    /**
     * Parses api transaction response for errors
     */
    private function parseApiTransactionResponse($apiResponse)
    {
        if (! $this->isTransactionSuccessful($apiResponse)) {
            $this->writeLog("Transaction with error: " . json_encode($apiResponse));
            return false;
        }

        return true;
    }

    /**
	 * Checks if the transaction was successful and
	 * the data was not tempered with.
     * 
     * @return bool
     */
    private function isTransactionSuccessful($apiResponse)
    {
        $matchCurrency = $this->order['currency_code'] == $apiResponse['amount']['currency'];
        $matchAmount = (string) $this->order['total'] == $apiResponse['amount']['decimal'];

        return (true == $apiResponse['authorisationCreated'] && $matchCurrency && $matchAmount);
    }

    /**
     * Gets errors from a failed api request
     * @param array $result The result returned by the api wrapper.
     * @return string
     */
    private function getResponseError($result)
    {
        $error = [];
        // if this is just one error
        if (isset($result['text'])) {
            return $result['text'];
        }

        if (isset($result['code']) && isset($result['error'])) {
            return $result['code'] . '-' . $result['error'];
        }

        // otherwise this is a multi field error
        if ($result) {
            foreach ($result as $fieldError) {
                $error[] = $fieldError['field'] . ':' . $fieldError['message'];
            }
        }

        return implode(' ', $error);
    }

    /**
     * @return mixed
     */
    private function getConfigValue($configKey)
    {
        return $this->config->get($this->paymentMethodConfigCode  . '_' . $configKey);
    }

    /**
     * 
     */
    private function writeLog($logMessage, $toErrorLog = false)
    {
        if ($this->getConfigValue('logging')) {
            $this->logger->write($logMessage);
        }
        if ($toErrorLog) {
            $this->log->write($logMessage);
        }
    }

    /**
     * 
     */
    private function getFormattedProducts()
    {
        $products = $this->cart->getProducts();
        $products_array = [];
        foreach ($products as $key => $product) {
            $products_array[] = [
                'ID'       => $product['product_id'],
                'name'     => $product['name'],
                'quantity' => $product['quantity']
            ];
        }

        return str_replace("\u0022","\\\\\"", json_encode($products_array, JSON_HEX_QUOT));
    }

    /**
     *
     */
    private function getTestObject(): array
    {
        return [
            "card"        => [
                "scheme"  => "supported",
                "code"    => "valid",
                "status"  => "valid",
                "limit"   => [
                    "decimal"  => "25000.99",
                    "currency" => $this->order['currency_code'],
                    
                ],
                "balance" => [
                    "decimal"  => "25000.99",
                    "currency" => $this->order['currency_code'],
                    
                ]
            ],
            "fingerprint" => "success",
            "tds"         => array(
                "fingerprint" => "success",
                "challenge"   => true,
                "status"      => "authenticated"
            ),
        ];
    }
}
