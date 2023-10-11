<?php


use Lunar\Lunar as ApiClient;

/**
 * 
 */
abstract class AbstractLunarFrontController extends \Controller
{
    const EXTENSION_PATH = '';
    const REMOTE_URL = 'https://pay.lunar.money/?id=';
    const TEST_REMOTE_URL = 'https://hosted-checkout-git-develop-lunar-app.vercel.app/?id=';

    protected string $paymentMethodCode = '';
    
    protected string $pluginVersion = '';
    protected ApiClient $lunarApiClient;

    protected $errors = [];
    protected string $intentIdKey = '_lunar_intent_id';
    protected bool $testMode = false;
    protected array $args = [];
    protected string $publicKey = '';

    /** @var int|string|null */
    protected $orderId;

    /**
     * 
     */
    public function index()
    {
        $this->pluginVersion = json_decode(file_get_contents(dirname(__DIR__) . '/composer.json'))->version;
        
        
        $this->load->language('extension/payment/lunar');
        $this->load->model(static::EXTENSION_PATH);

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

        $this->orderId = $this->session->data['order_id'];

        $this->setArgs();

        $data['button_confirm'] = $this->language->get('button_confirm');

        return $this->load->view(static::EXTENSION_PATH, $data);

    }

    /**
     * 
     */
    protected function setArgs()
    {
        if ($this->testMode) {
            $this->args['test'] = $this->getTestObject();
        }

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->orderId);

        $name = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
        $address = $order_info['payment_address_1'] . ', ';
        $address .= $order_info['payment_address_2'] != '' ? $order_info['payment_address_2'] . ', ' : '';
        $address .= $order_info['payment_city'] . ', ' . $order_info['payment_zone'] . ', ';
        $address .= $order_info['payment_country'] . ' - ' . $order_info['payment_postcode'];


        $this->args['amount'] = [
            'currency' => strtoupper($order_info['currency_code']),
            'decimal' => (string) $order_info['total'],
        ];

        $this->args['custom'] = [
			'products' => $this->getFormattedProducts(),
            'customer' => [
                'name' => $name,
                'email' => $order_info['email'],
                'telephone' => $order_info['telephone'],
                'address' => $address,
                'ip' => $order_info['ip'],
            ],
			'platform' => [
				'name' => 'Opencart',
				'version' => VERSION,
			],
			'lunarPluginVersion' => $this->pluginVersion,
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

        $this->args['redirectUrl'] = '';

        $this->args['preferredPaymentMethod'] = $this->paymentMethodCode;
    }

    /**
     * 
     */
    protected function savePaymentIntentOnTransaction($paymentIntentId)
    {
        
    }

    /**
     * 
     */
    protected function getPaymentIntentFromTransaction()
    {

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
                    "currency" => $this->context->currency->iso_code,
                    
                ],
                "balance" => [
                    "decimal"  => "25000.99",
                    "currency" => $this->context->currency->iso_code,
                    
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
