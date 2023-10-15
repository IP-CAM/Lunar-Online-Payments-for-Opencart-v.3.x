<?php

use Lunar\Lunar as ApiClient;

/**
 * 
 */
abstract class AbstractLunarAdminController extends \Controller
{
    const EXTENSION_PATH = '';
    const ADMIN_GENERAL_PATH = 'extension/payment/lunar';

    protected $error = array();
    protected $oc_token = '';
    protected $validationPublicKeys = ['live' => [], 'test' => []];

    protected string $storeId = '';
    protected string $paymentMethodCode = '';
    protected string $paymentMethodConfigCode = '';
    
    protected string $pluginVersion = '';
    protected ApiClient $lunarApiClient;


    public function index()
    {
        $this->oc_token = 'user_token=' . $this->session->data['user_token'];
        $this->load->language(static::EXTENSION_PATH);
        $this->load->model('tool/image');
        $this->load->model(self::ADMIN_GENERAL_PATH);
        $this->load->model('setting/setting');

        $this->pluginVersion = json_decode(file_get_contents(dirname(__DIR__) . '/composer.json'))->version;

        $this->storeId = $this->request->post['config_selected_store']
                            ?? $this->request->get['store_id'] 
                            ?? 0;

        $data['config_selected_store'] = $this->storeId;

        $this->maybeUpdateStoreSettings();

        $this->document->setTitle($this->language->get('heading_title'));

        $data['stores'] = $this->getAllStoresAsArray();

        $data['plugin_version'] = $this->pluginVersion;
        
        $this->setAdminTexts($data);

        // errors
        $this->maybeSetErrors($data, [
            'error_method_title',
            'error_public_key_test',
            'error_app_key_test',
            'error_public_key_live',
            'error_app_key_live',
            'error_logo_url',
            'error_configuration_id',
        ]);

        $this->setPostOrSettingValue($data, [
            'api_mode' => null,
            'app_key_test' => null,
            'public_key_test' => null,
            'app_key_live' => null,
            'public_key_live' => null,
            'capture_mode' => 'delayed',
            'logo_url' => null,
            'method_title' => $this->language->get('default_method_title'),
            'shop_title' => $this->config->get('config_meta_title'),
            'description' => null,
            'authorize_status_id' => 1, // pending
            'capture_status_id' => 5, // complete
            'refund_status_id' => 11, // refunded
            'cancel_status_id' => 9, // canceled reversal
            'status' => null,
            'logging' => null,
            'minimum_total' => 0,
            'geo_zone' => null,
            'sort_order' => 0,
        ]);


        $this->paymentMethodCode == 'card' ? $this->setPostOrSettingValue($data, [
            'checkout_cc_logo' => $this->language->get('default_checkout_cc_logo'),
        ]) : null;
        $this->paymentMethodCode == 'mobilePay' ? $this->setPostOrSettingValue($data, [
            'configuration_id' => null,
        ]) : null;
        
        
        $data['debugMode'] = isset($this->request->get['debug']) || ($this->getSettingValue('api_mode') == 'test');

        $this->maybeSetAlertMessages($data);

        $this->response->setOutput($this->load->view(self::ADMIN_GENERAL_PATH, $data));
    }

    /**
     * @return mixed
     */
    private function getSettingValue($key)
    {
        return $this->model_setting_setting->getSettingValue($this->paymentMethodConfigCode . '_' . $key, $this->storeId);
    }

    /**
     * 
     */
    private function maybeSetErrors(&$data, $errorKeys)
    {
        foreach ($errorKeys as $errorKey) {
            if (isset($this->error[$errorKey])) {
                $data[$errorKey] = $this->error[$errorKey];
            } else {
                $data[$errorKey] = '';
            }
        }
    }

    /**
     * 
     */
    private function maybeSetAlertMessages(&$data)
    {
        if (
            is_null($this->getSettingValue('method_title'))
            || is_null($this->getSettingValue('app_key_live'))
            || is_null($this->getSettingValue('public_key_live'))
        ) {
            $data['warning'] = $this->language->get('text_setting_review_required');
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } elseif (isset($this->session->data['error_warning']) && $this->session->data['error_warning'] != '') {
            $data['error_warning'] = $this->session->data['error_warning'];
            $this->session->data['error_warning'] = '';
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
    }

    /**
     * 
     */
    private function setPostOrSettingValue(&$data, $settingKeys)
    {
        foreach ($settingKeys as $settingKey => $default) {
            if (isset($this->request->post[$settingKey])) {
                $data[$settingKey] = $this->request->post[$settingKey];
            } else {
                $data[$settingKey] = $this->getSettingValue($settingKey);
            }
            
            if (!is_null($default)) {
                if (!is_null($this->getSettingValue($settingKey))) {
                    $data[$settingKey] = $this->getSettingValue($settingKey);
                } else {
                    $data[$settingKey] = $default;
                }
            }
        }
    }

    /**
     * 
     */
    private function validate()
    {
        if (! $this->user->hasPermission('modify', static::EXTENSION_PATH)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (($this->request->post['method_title'] ?? null) == '') {
            $this->error['error_method_title'] = $this->language->get('error_method_title');
        }

        $this->validateLogoURL();

        if ('mobilePay' == $this->paymentMethodCode) {
            $this->validateConfigId();
        }

        // key validation temporary disabled
        
        /** Include the last version API via autoloader */
        // require_once(DIR_SYSTEM . 'library/Lunar/vendor/autoload.php');
        
        // if ($this->request->post['payment_lunar_api_mode'] == 'live') {
        //     $error_app_key_live = $this->validateAppKeyField($this->request->post['payment_lunar_app_key_live'],'live');
        //     if($error_app_key_live){
        //         $this->error['error_app_key_live'] = $error_app_key_live;
        //     }

        //     $error_public_key_live = $this->validatePublicKeyField($this->request->post['payment_lunar_public_key_live'],'live');
        //     if($error_public_key_live){
        //         $this->error['error_public_key_live'] =$error_public_key_live;
        //     }

        // } else {
        //     $error_app_key_test = $this->validateAppKeyField($this->request->post['payment_lunar_app_key_test'],'test');
        //     if($error_app_key_test){
        //         $this->error['error_app_key_test'] = $error_app_key_test;
        //     }

        //     $error_public_key_test = $this->validatePublicKeyField($this->request->post['payment_lunar_public_key_test'],'test');
        //     if($error_public_key_test){
        //         $this->error['error_public_key_test'] = $error_public_key_test;
        //     }
        // }

        if (! is_numeric($this->request->post['minimum_total'] ?? null)) {
            $this->request->post['minimum_total'] = 0;
        }
        if (! is_numeric($this->request->post['sort_order'] ?? null)) {
            $this->request->post['sort_order'] = 0;
        }

        if ($this->error && ! isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }
    
        return ! $this->error;
    }

    /**
	 * @return void
	 */
	private function validateConfigId()
	{
        $configId = $this->request->post['configuration_id'] ?? '';
        
        if (! $configId) {
            $this->error['error_configuration_id'] = $this->language->get('error_config_id');
		
		} elseif (mb_strlen($configId) != 32) {
            $this->error['error_configuration_id'] = sprintf($this->language->get('error_config_id_len'), mb_strlen($configId));
        }
	}

    /**
	 * @return void
	 */
	private function validateLogoURL()
	{
        $url = $this->request->post['logo_url'] ?? '';

        if (! $url) {
            $this->error['error_logo_url'] = $this->language->get('error_logo_url_required');
		
		} elseif (! preg_match('/^https:\/\//', $url)) {
            $this->error['error_logo_url'] = $this->language->get('error_logo_url_https');
		
		} elseif (!$this->fileExists($url)) {
            $this->error['error_logo_url'] = $this->language->get('error_logo_url_invalid');
		}
	}


    /**
     * @return bool
     */
    private function fileExists(string $url)
    {
        $valid = true;

        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_HEADER, 1);
        curl_setopt($c, CURLOPT_NOBODY, 1);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_FRESH_CONNECT, 1);
        
        if(!curl_exec($c)){
            $valid = false;
        }

        curl_close($c);

        return $valid;
    }

    /**
     * Use custom function to get all opencart stores, including default.
     *
     * @return array
     */
    private function getAllStoresAsArray()
    {
        $storesArray   = [];
        /** Push default store to stores array. It's not extracted with getStores(). */
        $storesArray[] = [
            'store_id' => 0,
            'name'     => $this->config->get('config_name') . ' ' . $this->language->get('text_default'),
        ];

        // $storesArray[] = [
        //     'store_id' => 1,
        //     'name'     => 'TEST STORE',
        // ];

        $this->load->model('setting/store');
        $opencartStores = $this->model_setting_store->getStores();

        foreach ($opencartStores as $opencartStore) {
            $storesArray[] = array(
                'store_id' => $opencartStore['store_id'],
                'name'     => $opencartStore['name']
            );
        }

        return $storesArray;
    }

    /**
     * Update the settings for specific store
     */
    private function maybeUpdateStoreSettings()
    {
        if (( $this->request->server['REQUEST_METHOD'] != 'POST' ) || !$this->validate()) {
            return;
        }

        $this->load->model('setting/setting');

        $selectedStoreId = $this->request->post['config_selected_store'];
        unset($this->request->post['config_selected_store']);

        $updatedData = [];
        // map post keys to config keys
        foreach ($this->request->post as $k => $val) {
            $updatedData[$this->paymentMethodConfigCode . '_' . $k] = $val;
        }

        $this->model_setting_setting->editSetting($this->paymentMethodConfigCode, $updatedData, $selectedStoreId);
        
        $this->setDisabledStatusOnOtherStores();
        
        $this->session->data['success'] = $this->language->get('text_success');
        
        $redirect_url = $this->url->link(static::EXTENSION_PATH, 'store_id=' . $selectedStoreId . '&' . $this->oc_token . '&type=payment', true);
        $this->response->redirect($redirect_url);
    }

    /**
     * Set status = disabled on stores that not have settings yet (null).
     * @abstract If we save settings on one store, then in other store
     *           the payment method shows up, even if it is not set up
     * @return void
     */
    private function setDisabledStatusOnOtherStores()
    {
        $allStores = $this->getAllStoresAsArray();

        $statusKey = $this->paymentMethodConfigCode . '_status';

        foreach ($allStores as $store) {

            /** Get all store settings by store id. */
            $storePaymentMethodSettings = $this->model_setting_setting->getSetting($this->paymentMethodConfigCode, $store['store_id']);

            /** Check if status setting is not set, then set it on 0 (= disabled). */
            if (!isset($storePaymentMethodSettings[$statusKey])) {
                $this->model_setting_setting->editSetting($this->paymentMethodConfigCode, [$statusKey => 0], $store['store_id']);
            }
        }
    }

    
    /**
     * Validate the App key.
     *
     * @param string $value - the value of the input.
     *
     * @return string - the error message
     */
    protected function validateAppKeyField( $value, $mode ) {
        /** Check if the key value is empty **/
        if ( ! $value ) {
            return sprintf($this->language->get('error_app_key'),$mode);
        }
        /** Load the client from API**/
        $apiClient = new ApiClient( $value );
        try {
            /** Load the identity from API**/
            $identity = $apiClient->apps()->fetch();
        } catch ( \Paylike\Exception\ApiException $exception ) {
            $this->log->write(sprintf($this->language->get('error_app_key_invalid'),$mode));
            return sprintf($this->language->get('error_app_key_invalid'),$mode);
        }

        try {
            /** Load the merchants public keys list corresponding for current identity **/
            $merchants = $apiClient->merchants()->find( $identity['id'] );
            if ( $merchants ) {
                foreach ( $merchants as $merchant ) {
                    /** Check if the key mode is the same as the transaction mode **/
                    if(($mode == 'test' && $merchant['test']) || ($mode != 'test' && !$merchant['test'])){
                        $this->validationPublicKeys[$mode][] = $merchant['key'];
                    }
                }
            }
        } catch ( \Paylike\Exception\ApiException $exception ) {
            $this->log->write(sprintf($this->language->get('error_app_key_invalid'),$mode));
        }
        /** Check if public keys array for the current mode is populated **/
        if ( empty( $this->validationPublicKeys[$mode] ) ) {
            /** Generate the error based on the current mode **/
            $error = sprintf($this->language->get('error_app_key_invalid_mode'),$mode,array_values(array_diff(array_keys($this->validationPublicKeys), array($mode)))[0]);
            $this->log->write($error);

            return $error;
        }
    }

    /**
      * Validate the Public key.
      *
      * @param string $value - the value of the input.
      * @param string $mode - the transaction mode 'test' | 'live'.
      *
      * @return string - the error message
      */
    private function validatePublicKeyField($value, $mode) {
        /** Check if the key value is not empty **/
        if ( ! $value ) {
            return sprintf($this->language->get('error_public_key'),$mode);
        }
        /** Check if the local stored public keys array is empty OR the key is not in public keys list **/
        if ( empty( $this->validationPublicKeys[$mode] ) || ! in_array( $value, $this->validationPublicKeys[$mode] ) ) {
            $error = sprintf($this->language->get('error_public_key_invalid'),$mode);
            $this->log->write($error);

            return $error;
        }
    }

    /**
     * 
     */
    private function setAdminTexts(&$data)
    {
        $data['ccLogos'] = $this->model_extension_payment_lunar->getCcLogos();

        $data['breadcrumbs']   = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->oc_token, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', $this->oc_token . '&type=payment', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link(static::EXTENSION_PATH, $this->oc_token, true)
        );

        $data['action'] = $this->url->link(static::EXTENSION_PATH, $this->oc_token, true);
        $data['cancel'] = $this->url->link('marketplace/extension', $this->oc_token . '&type=payment', true);

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->setTexts($data, [
            'heading_title',
            'button_save',
            'button_cancel',
            'text_enabled',
            'text_disabled',
            'text_test',
            'text_live',
            'text_capture_instant',
            'text_capture_delayed',
            'text_description',
            'text_all_zones',
            'text_edit_settings',
            'text_general_settings',
            'text_advanced_settings',
            'entry_api_mode',
            'entry_public_key_test',
            'entry_app_key_test',
            'entry_public_key_live',
            'entry_app_key_live',
            'entry_capture_mode',
            'entry_logo_url',
            'entry_configuration_id',
            'entry_method_title',
            'entry_shop_title',
            'entry_checkout_cc_logo',
            'entry_authorize_status_id',
            'entry_capture_status_id',
            'entry_refund_status_id',
            'entry_cancel_status_id',
            'entry_payment_enabled',
            'entry_logging',
            'entry_minimum_total',
            'entry_geo_zone',
            'entry_sort_order',
            'entry_store',
            'help_api_mode',
            'help_public_key_test',
            'help_app_key_test',
            'help_public_key_live',
            'help_app_key_live',
            'help_capture_mode',
            'help_logo_url',
            'help_configuration_id',
            'help_method_title',
            'help_shop_title',
            'help_checkout_description',
            'help_checkout_cc_logo',
            'help_authorize_status_id',
            'help_capture_status_id',
            'help_refund_status_id',
            'help_cancel_status_id',
            'help_payment_enabled',
            'help_logging',
            'help_minimum_total',
            'help_geo_zone',
            'help_sort_order',
            'help_select_store',
        ]);
    }

    /**
     * 
     */
	private function setTexts(&$data, $textKeys)
    {
        foreach ($textKeys as $textKey) {
            $data[$textKey] = $this->language->get($textKey);
        }
	}

    /**
     * 
     */
	public function install()
    {
		if ($this->user->hasPermission('modify', 'marketplace/extension')) {
			$this->load->model(self::ADMIN_GENERAL_PATH);

            $this->model_extension_payment_lunar->install();
		}
	}

    /**
     * 
     */
	public function uninstall()
    {
		if ($this->user->hasPermission('modify', 'marketplace/extension')) {
			$this->load->model(self::ADMIN_GENERAL_PATH);

            $this->model_extension_payment_lunar->uninstall();
		}
	}
}
