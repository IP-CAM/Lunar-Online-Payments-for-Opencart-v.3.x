<?php

class ControllerExtensionPaymentLunar extends Controller
{
    const PLUGIN_VERSION = '1.0.0';
    const VENDOR_NAME = 'lunar';
    const CONFIG_CODE = 'payment_' . self::VENDOR_NAME;
    const EXTENSION_PATH = 'extension/payment/' . self::VENDOR_NAME;
    const THIS_EXTENSION_LIBRARY_PATH = DIR_SYSTEM . 'library' . DIRECTORY_SEPARATOR. 'Lunar' .  DIRECTORY_SEPARATOR;

    public function index()
    {
        $this->load->language(self::EXTENSION_PATH);
        $this->load->model(self::EXTENSION_PATH);
        $this->load->model('checkout/order');
        $data['plugin_version'] = self::PLUGIN_VERSION;
        $data['VERSION'] = VERSION;
        $data['active_mode']=$this->config->get(self::CONFIG_CODE . '_api_mode');

        if ($this->config->get(self::CONFIG_CODE . '_api_mode') == 'live') {
            $data[self::VENDOR_NAME . '_public_key'] = $this->config->get(self::CONFIG_CODE . '_public_key_live');
        } else {
            $data[self::VENDOR_NAME . '_public_key'] = $this->config->get(self::CONFIG_CODE . '_public_key_test');
        }

        if ($this->config->get(self::CONFIG_CODE . '_checkout_title') != '') {
            $data['popup_title'] = $this->config->get(self::CONFIG_CODE . '_checkout_title');
        } else {
            $data['popup_title'] = $this->config->get('config_name');
        }

        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['lc']             = $this->session->data['language'];
        $data['mode']           = $this->config->get(self::CONFIG_CODE . '_checkout_display_mode');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $order_info['currency_code'] = strtoupper($order_info['currency_code']);

        $data['order_id']  = $this->session->data['order_id'];
        $data['name']      = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
        $data['email']     = $order_info['email'];
        $data['telephone'] = $order_info['telephone'];

        $data['address'] = $order_info['payment_address_1'] . ', ';
        $data['address'] .= $order_info['payment_address_2'] != '' ? $order_info['payment_address_2'] . ', ' : '';
        $data['address'] .= $order_info['payment_city'] . ', ' . $order_info['payment_zone'] . ', ';
        $data['address'] .= $order_info['payment_country'] . ' - ' . $order_info['payment_postcode'];

        $data['ip']            = $order_info['ip'];
        $formattedAmount                = $this->getFormattedAmounts($order_info['total'], $order_info['currency_code']);
        $data['amount']        = $formattedAmount['in_minor'];
        $data['currency_code'] = $order_info['currency_code'];
        $data['exponent'] = $this->getExponentValueFromCurrencyCode($order_info['currency_code']);

        $products       = $this->cart->getProducts();
        $products_array = array();
        $products_label = array();
        $p              = 0;
        foreach ($products as $key => $product) {
            $products_array[ $p ] = array(
                'ID'       => $product['product_id'],
                'name'     => $product['name'],
                'quantity' => $product['quantity']
            );
            $products_label[ $p ] = $product['quantity'] . 'x ' . $product['name'];
            $p ++;
        }
        $data['products'] = json_encode($products_array);
        if ($this->config->get(self::CONFIG_CODE . '_checkout_description') != '') {
            $data['popup_description'] = $this->config->get(self::CONFIG_CODE . '_checkout_description');
        } else {
            $data['popup_description'] = implode(", & ", $products_label);
        }

        return $this->load->view(self::EXTENSION_PATH, $data);
    }

    public function process_payment()
    {
        $json = array();
        $this->load->language(self::EXTENSION_PATH);

        if (is_null($this->request->post['trans_ref']) || $this->request->post['trans_ref'] == '') {
            $json['error'] = $this->language->get('error_no_transaction_found');
        }

        if (! $json) {
            $json = $this->validate_payment();
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function validate_payment()
    {
        $this->load->language(self::EXTENSION_PATH);
        $this->logger = new Log(self::VENDOR_NAME . '.log');
        $log          = $this->config->get(self::CONFIG_CODE . '_logging') ? true : false;

        $json = array();
        $ref  = $this->request->post['trans_ref'];

        if ($log) {
            $this->logger->write('************');
        }
        if ($log) {
            $this->logger->write('Transaction validation. Transaction refference: ' . $ref);
        }

        $app_key = $this->config->get(self::CONFIG_CODE . '_api_mode') == 'live' ? $this->config->get(self::CONFIG_CODE . '_app_key_live') : $this->config->get(self::CONFIG_CODE . '_app_key_test');

        require_once(self::THIS_EXTENSION_LIBRARY_PATH . 'Client.php');
        Lunar\Client::setKey($app_key);

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $order_info['currency_code'] = strtoupper($order_info['currency_code']);
        $formattedAmount = $this->getFormattedAmounts($order_info['total'], $order_info['currency_code']);

        $trans_data = Lunar\Transaction::fetch($ref);

        if (is_null($trans_data)) {
            if ($log) {
                $this->logger->write('Invalid transaction data. Unable to authorize transaction.');
            }
            $json['error'] = $this->language->get('error_invalid_transaction_data');

            return $json;
        }

        if (is_array($trans_data) && isset($trans_data['error']) && ! is_null($trans_data['error']) && $trans_data['error'] == 1) {
            if ($log) {
                $this->logger->write('Transaction error returned: ' . $trans_data['message']);
            }
            $json['error'] = $this->language->get('error_transaction_error_returned');

            return $json;
        } elseif (is_array($trans_data) && isset($trans_data[0]['message']) && ! is_null($trans_data[0]['message'])) {
            if ($log) {
                $this->logger->write('Transaction error returned: ' . $trans_data[0]['message']);
            }
            $json['error'] = $this->language->get('error_transaction_error_returned');

            return $json;
        }

        if (isset($trans_data['transaction'])) {
            if (isset($trans_data['transaction']['successful']) && (strtoUpper($trans_data['transaction']['currency']) == $order_info['currency_code']) && ($trans_data['transaction']['amount'] == $formattedAmount['in_minor'])) {
                $order_captured = false;

                if ($this->config->get(self::CONFIG_CODE . '_capture_mode') == 'instant') {
                    $data         = array(
                        'amount'   => $formattedAmount['in_minor'],
                        'currency' => $order_info['currency_code']
                    );
                    $capture_data = Lunar\Transaction::capture($ref, $data);
                    if (! isset($capture_data['transaction'])) {
                        if ($log) {
                            $this->logger->write('Unable to capture amount of ' . $formattedAmount['formatted'] . ' (' . $order_info['currency_code'] . '). Order #' . $order_info['order_id'] . ' history updated.');
                        }
                    } else {
                        if ($log) {
                            $this->logger->write('Transaction finished. Captured amount: ' . $formattedAmount['formatted'] . ' (' . $order_info['currency_code'] . '). Order #' . $order_info['order_id'] . ' history updated.');
                        }
                        $order_captured = true;
                    }
                } else {
                    if ($log) {
                        $this->logger->write('Transaction authorized. Pending amount: ' . $formattedAmount['formatted'] . ' (' . $order_info['currency_code'] . '). Order #' . $order_info['order_id'] . ' history updated.');
                    }
                }

                if (! $order_captured) {
                    $type                = 'Authorize';
                    $transaction_amount  = 0;
                    $total_amount        = 0;
                    $comment             =  ucfirst(self::VENDOR_NAME) . ' transaction: ref:' . $ref . "\r\n" . 'Authorized amount: ' . $formattedAmount['formatted'] . ' (' . $order_info['currency_code'] . ')';
                    $new_order_status_id = $this->config->get(self::CONFIG_CODE . '_authorize_status_id');
                    $json['success']     = $this->language->get('success_message_authorized');
                } else {
                    $type                = 'Capture';
                    $transaction_amount  = $formattedAmount['converted'];
                    $total_amount        = $formattedAmount['converted'];
                    $comment             = ucfirst(self::VENDOR_NAME) . ' transaction: ref:' . $ref . "\r\n" . 'Captured amount: ' . $formattedAmount['formatted'] . ' (' . $order_info['currency_code'] . ')';
                    $new_order_status_id = $this->config->get(self::CONFIG_CODE . '_capture_status_id');
                    $json['success']     = $this->language->get('success_message_captured');
                }

                /** Insert new transaction. */
                $this->db->query("INSERT INTO `" . DB_PREFIX . self::VENDOR_NAME . "_transaction`
                                    SET order_id = '" . $order_info['order_id'] . "',
                                        transaction_id = '" . $ref . "',
                                        transaction_type = '" . $type . "',
                                        transaction_currency = '" . $order_info['currency_code'] . "',
                                        order_amount = '" . $formattedAmount['converted'] . "',
                                        transaction_amount = '" . $transaction_amount . "',
                                        total_amount = '" . $total_amount . "',
                                        history = '0',
                                        date_added = NOW()"
                                );

                $this->model_checkout_order->addOrderHistory($order_info['order_id'], $new_order_status_id, $comment);

                $json['redirect'] = $this->url->link('checkout/success', '', true);

                return $json;
            }
        }

        if ($log) {
            $this->logger->write('Transaction error. Empty transaction results.');
        }
        $json['error'] = $this->language->get('error_invalid_transaction_data');

        return $json;
    }

    private function getFormattedAmounts($order_amount, $currency_code)
    {
        $exponent = $this->getExponentValueFromCurrencyCode($currency_code);

        $multiplier = pow(10, $exponent);
        $formattedAmount     = array();

        $formattedAmount['order_amount']      = $order_amount;
        $formattedAmount['store_converted']   = $this->currency->format($formattedAmount['order_amount'], $currency_code, false, false);
        $formattedAmount['store_formatted']   = $this->currency->format($formattedAmount['order_amount'], $currency_code, false, true);
        $formattedAmount['in_minor']           = ceil(round($formattedAmount['store_converted'] * $multiplier));
        $formattedAmount['converted'] = $this->currency->format($formattedAmount['in_minor'] / $multiplier, $currency_code, 1, false);
        $formattedAmount['formatted'] = $this->currency->format($formattedAmount['in_minor'] / $multiplier, $currency_code, 1, true);

        return $formattedAmount;
    }

    private function getExponentValueFromCurrencyCode($currency_code)
    {
        $exponent_zero  = array(
            'BIF',
            'BYR',
            'DJF',
            'GNF',
            'JPY',
            'KMF',
            'KRW',
            'PYG',
            'RWF',
            'VND',
            'VUV',
            'XAF',
            'XOF',
            'XPF'
        );

        $exponent_three = array('BHD', 'IQD', 'JOD', 'KWD', 'OMR', 'TND');

        $exponent       = 2;

        if (in_array($currency_code, $exponent_zero)) {
            $exponent = 0;
        } elseif (in_array($currency_code, $exponent_three)) {
            $exponent = 3;
        }

        return $exponent;
    }
}
