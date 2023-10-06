<?php

class ControllerExtensionPaymentLunar extends Controller
{
    const PLUGIN_VERSION = '1.0.0';
    const EXTENSION_PATH = 'extension/payment/lunar';
    const CARD_METHOD = 'card';
    const MOBILEPAY_METHOD = 'mobilePay';

    private $error = array();
    private $oc_token = '';
    private $validationPublicKeys = ['live' => [], 'test' => []];

    public function index()
    {
        $this->oc_token = 'user_token';
        $this->load->language(self::EXTENSION_PATH);
        $this->load->model('tool/image');
        $this->load->model(self::EXTENSION_PATH);

        $this->model_extension_payment_lunar->install();

        if (
            is_null($this->config->get('payment_lunar_method_title'))
            || is_null($this->config->get('payment_lunar_app_key_live'))
            || is_null($this->config->get('payment_lunar_public_key_live'))
        ) {
            $data['warning'] = $this->language->get('text_setting_review_required');
        }

        $this->maybeUpdateStoreSettings();

        $this->document->setTitle($this->language->get('heading_title'));

        /** Get all opencart stores, including default. */
        $data['stores'] = $this->getAllStoresAsArray();
        
        $data['plugin_version'] = self::PLUGIN_VERSION;
        
        $data['heading_title'] = $this->language->get('heading_title');

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


        if (isset($this->error['error_payment_method_title'])) {
            $data['error_payment_method_title'] = $this->error['error_payment_method_title'];
        } else {
            $data['error_payment_method_title'] = '';
        }

        if (isset($this->error['error_public_key_test'])) {
            $data['error_public_key_test'] = $this->error['error_public_key_test'];
        } else {
            $data['error_public_key_test'] = '';
        }

        if (isset($this->error['error_app_key_test'])) {
            $data['error_app_key_test'] = $this->error['error_app_key_test'];
        } else {
            $data['error_app_key_test'] = '';
        }

        if (isset($this->error['error_public_key_live'])) {
            $data['error_public_key_live'] = $this->error['error_public_key_live'];
        } else {
            $data['error_public_key_live'] = '';
        }

        if (isset($this->error['error_app_key_live'])) {
            $data['error_app_key_live'] = $this->error['error_app_key_live'];
        } else {
            $data['error_app_key_live'] = '';
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } elseif (isset($this->session->data['error_warning']) && $this->session->data['error_warning'] != '') {
            $data['error_warning']                = $this->session->data['error_warning'];
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

        if (isset($this->request->post['payment_lunar_method_title'])) {
            $data['payment_lunar_method_title'] = $this->request->post['payment_lunar_method_title'];
        } elseif (! is_null($this->config->get('payment_lunar_method_title'))) {
            $data['payment_lunar_method_title'] = $this->config->get('payment_lunar_method_title');
        } else {
            $data['payment_lunar_method_title'] = $this->language->get('default_payment_method_title');
        }

        if (isset($this->request->post['payment_lunar_shop_title'])) {
            $data['payment_lunar_shop_title'] = $this->request->post['payment_lunar_shop_title'];
        } else {
            $data['payment_lunar_shop_title'] = $this->config->get('payment_lunar_shop_title');
        }

        if (isset($this->request->post['payment_lunar_description'])) {
            $data['payment_lunar_description'] = $this->request->post['payment_lunar_description'];
        } else {
            $data['payment_lunar_description'] = $this->config->get('payment_lunar_description');
        }

        $data['ccLogos'] = $this->model_extension_payment_lunar->getCcLogos();
        if (isset($this->request->post['payment_lunar_checkout_cc_logo'])) {
            $data['payment_lunar_checkout_cc_logo'] = $this->request->post['payment_lunar_checkout_cc_logo'];
        } elseif (! is_null($this->config->get('payment_lunar_checkout_cc_logo'))) {
            $data['payment_lunar_checkout_cc_logo'] = $this->config->get('payment_lunar_checkout_cc_logo');
        } else {
            $data['payment_lunar_checkout_cc_logo'] = $this->language->get('default_payment_lunar_checkout_cc_logo');
        }

        if (isset($this->request->post['payment_lunar_app_key_test'])) {
            $data['payment_lunar_app_key_test'] = $this->request->post['payment_lunar_app_key_test'];
        } else {
            $data['payment_lunar_app_key_test'] = $this->config->get('payment_lunar_app_key_test');
        }

        if (isset($this->request->post['payment_lunar_public_key_test'])) {
            $data['payment_lunar_public_key_test'] = $this->request->post['payment_lunar_public_key_test'];
        } else {
            $data['payment_lunar_public_key_test'] = $this->config->get('payment_lunar_public_key_test');
        }

        if (isset($this->request->post['payment_lunar_app_key_live'])) {
            $data['payment_lunar_app_key_live'] = $this->request->post['payment_lunar_app_key_live'];
        } else {
            $data['payment_lunar_app_key_live'] = $this->config->get('payment_lunar_app_key_live');
        }

        if (isset($this->request->post['payment_lunar_public_key_live'])) {
            $data['payment_lunar_public_key_live'] = $this->request->post['payment_lunar_public_key_live'];
        } else {
            $data['payment_lunar_public_key_live'] = $this->config->get('payment_lunar_public_key_live');
        }

        if (isset($this->request->post['payment_lunar_api_mode'])) {
            $data['payment_lunar_api_mode'] = $this->request->post['payment_lunar_api_mode'];
        } else {
            $data['payment_lunar_api_mode'] = $this->config->get('payment_lunar_api_mode');
        }

        if (isset($this->request->post['payment_lunar_capture_mode'])) {
            $data['payment_lunar_capture_mode'] = $this->request->post['payment_lunar_capture_mode'];
        } elseif (! is_null($this->config->get('payment_lunar_capture_mode'))) {
            $data['payment_lunar_capture_mode'] = $this->config->get('payment_lunar_capture_mode');
        } else {
            $data['payment_lunar_capture_mode'] = 'instant';
        }

        if (isset($this->request->post['payment_lunar_authorize_status_id'])) {
            $data['payment_lunar_authorize_status_id'] = $this->request->post['payment_lunar_authorize_status_id'];
        } elseif (! is_null($this->config->get('payment_lunar_authorize_status_id'))) {
            $data['payment_lunar_authorize_status_id'] = $this->config->get('payment_lunar_authorize_status_id');
        } else {
            $data['payment_lunar_authorize_status_id'] = 1;
        }

        if (isset($this->request->post['payment_lunar_capture_status_id'])) {
            $data['payment_lunar_capture_status_id'] = $this->request->post['payment_lunar_capture_status_id'];
        } elseif (! is_null($this->config->get('payment_lunar_capture_status_id'))) {
            $data['payment_lunar_capture_status_id'] = $this->config->get('payment_lunar_capture_status_id');
        } else {
            $data['payment_lunar_capture_status_id'] = 5;
        }

        if (isset($this->request->post['payment_lunar_refund_status_id'])) {
            $data['payment_lunar_refund_status_id'] = $this->request->post['payment_lunar_refund_status_id'];
        } elseif (! is_null($this->config->get('payment_lunar_refund_status_id'))) {
            $data['payment_lunar_refund_status_id'] = $this->config->get('payment_lunar_refund_status_id');
        } else {
            $data['payment_lunar_refund_status_id'] = 11;
        }

        if (isset($this->request->post['payment_lunar_cancel_status_id'])) {
            $data['payment_lunar_cancel_status_id'] = $this->request->post['payment_lunar_cancel_status_id'];
        } elseif (! is_null($this->config->get('payment_lunar_cancel_status_id'))) {
            $data['payment_lunar_cancel_status_id'] = $this->config->get('payment_lunar_cancel_status_id');
        } else {
            $data['payment_lunar_cancel_status_id'] = 16;
        }

        if (isset($this->request->post['payment_lunar_status'])) {
            $data['payment_lunar_status'] = $this->request->post['payment_lunar_status'];
        } else {
            $data['payment_lunar_status'] = $this->config->get('payment_lunar_status');
        }

        if (isset($this->request->post['payment_lunar_logging'])) {
            $data['payment_lunar_logging'] = $this->request->post['payment_lunar_logging'];
        } else {
            $data['payment_lunar_logging'] = $this->config->get('payment_lunar_logging');
        }

        if (isset($this->request->post['payment_lunar_minimum_total'])) {
            $data['payment_lunar_minimum_total'] = $this->request->post['payment_lunar_minimum_total'];
        } elseif (! is_null($this->config->get('payment_lunar_minimum_total'))) {
            $data['payment_lunar_minimum_total'] = $this->config->get('payment_lunar_minimum_total');
        } else {
            $data['payment_lunar_minimum_total'] = 0;
        }

        if (isset($this->request->post['payment_lunar_geo_zone'])) {
            $data['payment_lunar_geo_zone'] = $this->request->post['payment_lunar_geo_zone'];
        } else {
            $data['payment_lunar_geo_zone'] = $this->config->get('payment_lunar_geo_zone');
        }

        if (isset($this->request->post['payment_lunar_sort_order'])) {
            $data['payment_lunar_sort_order'] = $this->request->post['payment_lunar_sort_order'];
        } elseif (! is_null($this->config->get('payment_lunar_sort_order'))) {
            $data['payment_lunar_sort_order'] = $this->config->get('payment_lunar_sort_order');
        } else {
            $data['payment_lunar_sort_order'] = 0;
        }

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
            'href' => $this->url->link(self::EXTENSION_PATH, $this->oc_token . '=' . $this->session->data[ $this->oc_token ], true)
        );

        $data['action']          = $this->url->link(self::EXTENSION_PATH, $this->oc_token . '=' . $this->session->data[ $this->oc_token ], true);
        $data['cancel']          = $this->url->link('marketplace/extension', $this->oc_token . '=' . $this->session->data[ $this->oc_token ] . '&type=payment', true);

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $data['debugMode'] = isset($this->request->get['debug']) || ($this->config->get('payment_lunar_api_mode') == 'test');


        $data ['lunar_card_setting_fields'] = $this->load->view(self::EXTENSION_PATH . '_' . self::CARD_METHOD . '_setting', $data);
        $data ['lunar_mobilePay_setting_fields'] = $this->load->view(self::EXTENSION_PATH . '_' . self::MOBILEPAY_METHOD . '_setting', $data);

        $this->response->setOutput($this->load->view(self::EXTENSION_PATH, $data));
    }


    protected function validate()
    {
        if (! $this->user->hasPermission('modify', self::EXTENSION_PATH)) {
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

        return $settingModel->getSetting('payment_lunar', $storeId);
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

        $this->model_setting_setting->editSetting('payment_lunar', $this->request->post, $selectedStoreId);
        $redirect_url = $this->url->link('marketplace/extension', $this->oc_token . '=' . $this->session->data[ $this->oc_token ] . '&type=payment', true);

        $this->setDisabledStatusOnOtherStores('payment_lunar');

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
     * @param string $mode - the transaction mode 'test' | 'live'.
     *
     * @return string - the error message
     */
    protected function validateAppKeyField( $value, $mode ) {
        /** Check if the key value is empty **/
        if ( ! $value ) {
            return sprintf($this->language->get('error_app_key'),$mode);
        }
        /** Load the client from API**/
        $apiClient = new Paylike\Paylike( $value );
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

}
