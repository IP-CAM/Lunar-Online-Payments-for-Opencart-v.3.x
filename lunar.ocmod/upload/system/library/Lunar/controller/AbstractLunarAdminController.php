<?php

use Lunar\Lunar as ApiClient;

/**
 * 
 */
abstract class AbstractLunarAdminController extends \Controller
{
    const EXTENSION_PATH = '';

    protected $error = array();
    protected $oc_token = '';
    protected $validationPublicKeys = ['live' => [], 'test' => []];

    protected string $paymentMethodCode = '';
    
    protected string $pluginVersion = '';
    protected ApiClient $lunarApiClient;


    public function index()
    {
        $this->oc_token = 'user_token';
        $this->load->language('extension/payment/lunar');
        $this->load->model('tool/image');
        $this->load->model('extension/payment/lunar');

        $this->model_extension_payment_lunar->install();

        // $libraryPath = DIR_SYSTEM . 'library' . DIRECTORY_SEPARATOR. 'Lunar' .  DIRECTORY_SEPARATOR;
        // $this->pluginVersion = json_decode(file_get_contents($libraryPath . 'composer.json'))->version;


        $this->maybeUpdateStoreSettings();

        $this->document->setTitle($this->language->get('heading_title'));

        /** Get all opencart stores, including default. */
        $data['stores'] = $this->getAllStoresAsArray();
        
        // $data['plugin_version'] = $this->pluginVersion;
        
        $this->setAdminTexts($data);

        // errors
        $this->maybeSetError('error_payment_method_title', $data);
        $this->maybeSetError('error_public_key_test', $data);
        $this->maybeSetError('error_app_key_test', $data);
        $this->maybeSetError('error_public_key_live', $data);
        $this->maybeSetError('error_app_key_live', $data);

        // post / config
        $this->setPostOrConfigValue('method_title', $data, $this->language->get('default_payment_method_title'));
        $this->setPostOrConfigValue('shop_title', $data);
        $this->setPostOrConfigValue('description', $data);
        $this->setPostOrConfigValue('api_mode', $data);
        $this->setPostOrConfigValue('app_key_test', $data);
        $this->setPostOrConfigValue('public_key_test', $data);
        $this->setPostOrConfigValue('app_key_live', $data);
        $this->setPostOrConfigValue('public_key_live', $data);
        $this->setPostOrConfigValue('capture_mode', $data, 'instant');
        $this->setPostOrConfigValue('authorize_status_id', $data, 1);
        $this->setPostOrConfigValue('capture_status_id', $data, 5);
        $this->setPostOrConfigValue('refund_status_id', $data, 11);
        $this->setPostOrConfigValue('cancel_status_id', $data, 16);
        $this->setPostOrConfigValue('status', $data);
        $this->setPostOrConfigValue('logging', $data);
        $this->setPostOrConfigValue('minimum_total', $data, 0);
        $this->setPostOrConfigValue('geo_zone', $data);
        $this->setPostOrConfigValue('sort_order', $data, 0);
        $this->setPostOrConfigValue('checkout_cc_logo', $data, $this->language->get('default_payment_lunar_checkout_cc_logo'));

        $data['ccLogos'] = $this->model_extension_payment_lunar->getCcLogos();

        /** Select option in stores dropdown field. */
        if (isset($this->request->post['config_selected_store'])) {
            $data['config_selected_store'] = $this->request->post['config_selected_store'];
        } else {
            $data['config_selected_store'] = 0;
        }

        $data['breadcrumbs']   = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->oc_token . '=' . $this->session->data[ $this->oc_token ], true)
        );


        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', $this->oc_token . '=' . $this->session->data[ $this->oc_token ] . '&type=payment', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link(static::EXTENSION_PATH, $this->oc_token . '=' . $this->session->data[ $this->oc_token ], true)
        );

        $data['action'] = $this->url->link(static::EXTENSION_PATH, $this->oc_token . '=' . $this->session->data[ $this->oc_token ], true);
        $data['cancel'] = $this->url->link('marketplace/extension', $this->oc_token . '=' . $this->session->data[ $this->oc_token ] . '&type=payment', true);

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $data['debugMode'] = isset($this->request->get['debug']) || ($this->config->get('payment_lunar_api_mode') == 'test');


        if (
            is_null($this->config->get('payment_lunar_method_title'))
            || is_null($this->config->get('payment_lunar_app_key_live'))
            || is_null($this->config->get('payment_lunar_public_key_live'))
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

        $this->response->setOutput($this->load->view('extension/payment/lunar', $data));
    }

    /**
     * 
     */
    private function maybeSetError($key, &$data)
    {
        if (isset($this->error[$key])) {
            $data[$key] = $this->error[$key];
        } else {
            $data[$key] = '';
        }
    }

    /**
     * 
     */
    private function setPostOrConfigValue($key, &$data, $default = '')
    {
        $configKey = 'payment_lunar_' . $this->paymentMethodCode . '_' . $key;

        if (isset($this->request->post[$configKey])) {
            $data[$configKey] = $this->request->post[$configKey];
        } else {
            $data[$configKey] = $this->config->get($configKey);
        }
        
        if ($default) {
            if (!is_null($this->config->get($configKey))) {
                $data[$configKey] = $this->config->get($configKey);
            } else {
                $data[$configKey] = $default;
            }
        }
    }


    protected function validate()
    {
        if (! $this->user->hasPermission('modify', static::EXTENSION_PATH)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        if ($this->request->post['payment_lunar_method_title'] == '') {
            $this->error['error_payment_method_title'] = $this->language->get('error_payment_method_title');
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

        if (! is_numeric($this->request->post['payment_lunar_minimum_total'])) {
            $this->request->post['payment_lunar_minimum_total'] = 0;
        }
        if (! is_numeric($this->request->post['payment_lunar_sort_order'])) {
            $this->request->post['payment_lunar_sort_order'] = 0;
        }
        if ($this->error && ! isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        return ! $this->error;
    }


    /**
     * Get module settings for chosen store
     */
    public function get_store_settings()
    {
        /** Check if request is POST and store_id is set. */
        if (('POST' == $this->request->server['REQUEST_METHOD']) && isset($this->request->post['store_id'])) {
            $storeId = $this->request->post['store_id'];

            echo json_encode($this->getSettingsData($storeId));

        } else {
            /** Returns an object with key as vendor_data_error. */
            echo '{"lunar_data_error": "Operation not allowed"}';
        }
    }


    /** Get module settings data by store id. */
    private function getSettingsData($storeId)
    {
        /** Load setting model. */
        $this->load->model('setting/setting');
        $settingModel = $this->model_setting_setting;

        return $settingModel->getSetting('payment_lunar_' . $this->paymentMethodCode, $storeId);
    }

    /**
     * Use custom function to get all opencart stores, including default.
     *
     * @return array
     */
    private function getAllStoresAsArray()
    {
        $storesArray   = [];
        /** Push default store to stores array. It is not extracted with getStores(). */
        $storesArray[] = [
            'store_id' => 0,
            'name'     => $this->config->get('config_name') . ' ' . $this->language->get('text_default'),
        ];

        $this->load->model('setting/store');
        /** Extract OpenCart stores. */
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

        /** Get store ID & Update selected store settings for this module. */
        $selectedStoreId = $this->request->post['config_selected_store'];

        $this->model_setting_setting->editSetting('payment_lunar_' . $this->paymentMethodCode, $this->request->post, $selectedStoreId);
        $redirect_url = $this->url->link('marketplace/extension', $this->oc_token . '=' . $this->session->data[ $this->oc_token ] . '&type=payment', true);

        $this->setDisabledStatusOnOtherStores('payment_lunar_' . $this->paymentMethodCode);

        $this->session->data['success'] = $this->language->get('text_success');

        $this->response->redirect($redirect_url);
    }

    /**
     * Set status = disabled on stores that not have settings yet (null).
     * @abstract If we save settings on one store, then in other store
     *           the payment method shows up, even if it is not set up
     *
     * @param string $pluginSettingsCode
     * @return void
     */
    private function setDisabledStatusOnOtherStores($pluginSettingsCode)
    {
        $allStores = $this->getAllStoresAsArray();

        $pluginStatusStringKey = $pluginSettingsCode . '_status';

        foreach ($allStores as $store) {

            /** Get all store settings by store id. */
            $storePluginSettings = $this->model_setting_setting->getSetting($pluginSettingsCode, $store['store_id']);

            /** Check if status setting is not set, then set it on 0 (= disabled). */
            if (!isset($storePluginSettings[$pluginStatusStringKey])) {
                $this->model_setting_setting->editSetting($pluginSettingsCode, [$pluginStatusStringKey => 0], $store['store_id']);
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
    protected function validatePublicKeyField($value, $mode) {
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
    protected function getPaymentIntentFromTransaction()
    {

    }
    
    /**
     * @return mixed
     */
    protected function getConfigValue($configKey)
    {
        return $this->config->get('payment_lunar_' . $this->paymentMethodCode . '_' . $configKey);
    }


    private function setAdminTexts(&$data)
    {
        $data['heading_title']                  = $this->language->get('heading_title');

        $data['button_save']                     = $this->language->get('button_save');
        $data['button_cancel']                   = $this->language->get('button_cancel');

        $data['text_enabled']                    = $this->language->get('text_enabled');
        $data['text_disabled']                   = $this->language->get('text_disabled');
        $data['text_test']                       = $this->language->get('text_test');
        $data['text_live']                       = $this->language->get('text_live');
        $data['text_all_zones']                  = $this->language->get('text_all_zones');
        $data['text_description']                = $this->language->get('text_description');
        $data['text_edit_settings']              = $this->language->get('text_edit_settings');
        $data['text_general_settings']           = $this->language->get('text_general_settings');
        $data['text_advanced_settings']          = $this->language->get('text_advanced_settings');
        $data['text_capture_instant']            = $this->language->get('text_capture_instant');
        $data['text_capture_delayed']            = $this->language->get('text_capture_delayed');

        $data['entry_payment_method_title']      = $this->language->get('entry_payment_method_title');
        $data['entry_shop_title']                = $this->language->get('entry_shop_title');
        $data['entry_checkout_cc_logo']          = $this->language->get('entry_checkout_cc_logo');
        $data['entry_checkout_display_mode']     = $this->language->get('entry_checkout_display_mode');
        $data['entry_public_key_test']           = $this->language->get('entry_public_key_test');
        $data['entry_app_key_test']              = $this->language->get('entry_app_key_test');
        $data['entry_public_key_live']           = $this->language->get('entry_public_key_live');
        $data['entry_app_key_live']              = $this->language->get('entry_app_key_live');
        $data['entry_api_mode']                  = $this->language->get('entry_api_mode');
        $data['entry_capture_mode']              = $this->language->get('entry_capture_mode');
        $data['entry_authorize_status_id']       = $this->language->get('entry_authorize_status_id');
        $data['entry_capture_status_id']         = $this->language->get('entry_capture_status_id');
        $data['entry_refund_status_id']          = $this->language->get('entry_refund_status_id');
        $data['entry_cancel_status_id']          = $this->language->get('entry_cancel_status_id');
        $data['entry_payment_enabled']           = $this->language->get('entry_payment_enabled');
        $data['entry_logging']                   = $this->language->get('entry_logging');
        $data['entry_minimum_total']             = $this->language->get('entry_minimum_total');
        $data['entry_geo_zone']                  = $this->language->get('entry_geo_zone');
        $data['entry_sort_order']                = $this->language->get('entry_sort_order');
        $data['entry_store']                     = $this->language->get('entry_store');

        $data['help_payment_method_title']       = $this->language->get('help_payment_method_title');
        $data['help_shop_title']                 = $this->language->get('help_shop_title');
        $data['help_checkout_description']       = $this->language->get('help_checkout_description');
        $data['help_checkout_cc_logo']           = $this->language->get('help_checkout_cc_logo');
        $data['help_public_key_test']            = $this->language->get('help_public_key_test');
        $data['help_app_key_test']               = $this->language->get('help_app_key_test');
        $data['help_public_key_live']            = $this->language->get('help_public_key_live');
        $data['help_app_key_live']               = $this->language->get('help_app_key_live');
        $data['help_api_mode']                   = $this->language->get('help_api_mode');
        $data['help_capture_mode']               = $this->language->get('help_capture_mode');
        $data['help_authorize_status_id']        = $this->language->get('help_authorize_status_id');
        $data['help_capture_status_id']          = $this->language->get('help_capture_status_id');
        $data['help_refund_status_id']           = $this->language->get('help_refund_status_id');
        $data['help_cancel_status_id']           = $this->language->get('help_cancel_status_id');
        $data['help_payment_enabled']            = $this->language->get('help_payment_enabled');
        $data['help_logging']                    = $this->language->get('help_logging');
        $data['help_minimum_total']              = $this->language->get('help_minimum_total');
        $data['help_geo_zone']                   = $this->language->get('help_geo_zone');
        $data['help_sort_order']                 = $this->language->get('help_sort_order');
        $data['help_select_store']               = $this->language->get('help_select_store');
    }


}
