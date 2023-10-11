<?php

require_once(DIR_SYSTEM . 'library/Lunar/vendor/autoload.php');


use Lunar\Lunar as ApiClient;

/**
 * 
 */
abstract class AbstractLunarFrontController extends \Controller
{
    const EXTENSION_PATH = '';
    const REMOTE_URL = 'https://pay.lunar.money/?id=';
    const TEST_REMOTE_URL = 'https://hosted-checkout-git-develop-lunar-app.vercel.app/?id=';
    const LUNAR_DB_TABLE = 'lunar_transaction';

    protected string $paymentMethodCode = '';

    protected ApiClient $lunarApiClient;

    protected $errors = [];
    protected string $intentIdKey = '_lunar_intent_id';
    protected bool $testMode = false;
    protected array $args = [];
    protected string $publicKey = '';
    protected ?string $paymentIntentId = null;
    protected array $dbTransaction = [];

    /** @var int|string|null */
    protected $orderId;

    /**
     * 
     */
    public function index()
    {
 
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['paymentRedirectUrl'] = $this->url->link(static::EXTENSION_PATH . '/redirect');

        return $this->load->view('extension/payment/lunar', $data);
    }

    /**
     * 
     */
    public function init()
    {   
        $this->load->language('extension/payment/lunar');
        $this->load->model(static::EXTENSION_PATH);
        $this->load->model('extension/payment/lunar_transaction');
        $this->load->model('checkout/order');

        $this->order = $this->model_checkout_order->getOrder($this->orderId);

        $this->testMode = 'test' == $this->getConfigValue('api_mode');
        if ($this->testMode) {
            $this->publicKey =  $this->getConfigValue('public_key_test');
            $privateKey =  $this->getConfigValue('secret_key_test');
        } else {
            $this->publicKey = $this->getConfigValue('public_key_live');
            $privateKey = $this->getConfigValue('secret_key_live');
        }

        /** API Client instance */
        $this->lunarApiClient = new ApiClient($privateKey);
    }

    /**
     * 
     */
    public function redirect()
    {
        $this->orderId = $this->session->data['order_id'];

        $this->init();
        
        $this->setArgs();


        $this->paymentIntentId = $this->getPaymentIntentFromTransaction($this->dbTransaction);
        if (!$this->paymentIntentId) {
            $this->paymentIntentId = $this->lunarApiClient->payments()->create($this->args);
        }

        if (empty($this->paymentIntentId)) {
            $data['error'] = 'An error occurred creating payment intent for order. Please try again or contact system administrator.';
        }

        $this->savePaymentIntentOnTransaction();

        $redirectUrl = self::REMOTE_URL . $this->paymentIntentId;
        if(isset($this->args['test'])) {
            $redirectUrl = self::TEST_REMOTE_URL . $this->paymentIntentId;
        }

        $data['redirect'] = $redirectUrl;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

    /**
     * 
     */
    public function callback()
    {
        $this->init();
        //$this->orderId = $this->session->data['order_id'];

        $this->orderId = $this->request->get['order_id'];

        // ............................
    }

    /**
     * 
     */
    protected function setArgs()
    {
        $pluginVersion = json_decode(file_get_contents(dirname(__DIR__) . '/composer.json'))->version;

        if ($this->testMode) {
            $this->args['test'] = $this->getTestObject();
        }

        $order = $this->order;

        $name = $order['payment_firstname'] . ' ' . $order['payment_lastname'];
        $address = $order['payment_address_1'] . ', ';
        $address .= $order['payment_address_2'] != '' ? $order['payment_address_2'] . ', ' : '';
        $address .= $order['payment_city'] . ', ' . $order['payment_zone'] . ', ';
        $address .= $order['payment_country'] . ' - ' . $order['payment_postcode'];


        $this->args['amount'] = [
            'currency' => strtoupper($order['currency_code']),
            'decimal' => (string) $order['total'],
        ];

        $this->args['custom'] = [
			'products' => $this->getFormattedProducts(),
            'customer' => [
                'name' => $name,
                'email' => $order['email'],
                'telephone' => $order['telephone'],
                'address' => $address,
                'ip' => $order['ip'],
            ],
			'platform' => [
				'name' => 'Opencart',
				'version' => VERSION,
			],
			'lunarPluginVersion' => $pluginVersion,
        ];

        $this->args['integration'] = [
            'key' => $this->publicKey,
            'name' => $this->getConfigValue('shop_title'),
            'logo' => $this->getConfigValue('logo_url'),
        ];

        if ($this->getConfigValue('configuration_id')) {
            $this->args['mobilePayConfiguration'] = [
                'configurationID' => $this->getConfigValue('configuration_id'),
                'logo' => $this->getConfigValue('logo_url'),
            ];
        }

        $this->args['redirectUrl'] = $this->url->link(
            static::EXTENSION_PATH . '/callback', 
            'order_id=' . $this->orderId
        );

        $this->args['preferredPaymentMethod'] = $this->paymentMethodCode;
    }

    /**
     * 
     */
    protected function savePaymentIntentOnTransaction()
    {
        $this->db->query("DELETE FROM `" . self::LUNAR_DB_TABLE . "`
                            WHERE order_id = '" . $this->orderId . "'
                            AND transaction_type = 'INIT'");

        $this->db->query("INSERT INTO `" . self::LUNAR_DB_TABLE . "`
                            SET order_id = '" . $this->orderId . "',
                                transaction_id = '" . $this->paymentIntentId . "',
                                transaction_type = 'INIT',
                                transaction_currency = '" . $this->order['currency_code'] . "',
                                order_amount = '" . $this->order['total'] . "',
                                transaction_amount = '0',
                                history = '0',
                                date_added = NOW()"
                        );
    }

    /**
     * 
     */
    protected function getPaymentIntentFromTransaction()
    {
        $initTransaction = $this->model_extension_payment_lunar_transaction->getLastTransaction($this->orderId);
        return ($initTransaction && $initTransaction['transaction_type'] == 'INIT')
                ? $initTransaction['transaction_id']
                : null;
    }
    
    /**
     * @return mixed
     */
    protected function getConfigValue($configKey)
    {
        return $this->config->get('payment_lunar_' . $this->paymentMethodCode  . '_' . $configKey);
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
                    "currency" => strtoupper($this->order['currency_code']),
                    
                ],
                "balance" => [
                    "decimal"  => "25000.99",
                    "currency" => strtoupper($this->order['currency_code']),
                    
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
