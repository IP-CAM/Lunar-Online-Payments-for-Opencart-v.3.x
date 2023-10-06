<?php

class ControllerExtensionPaymentLunar extends Controller
{
    const PLUGIN_VERSION = '1.0.0';
    const EXTENSION_PATH = 'extension/payment/lunar';

    public function index()
    {
        $this->load->language(self::EXTENSION_PATH);
        $this->load->model(self::EXTENSION_PATH);
        $this->load->model('checkout/order');
        
        $data['plugin_version'] = self::PLUGIN_VERSION;
        $data['opencart_version'] = VERSION;

        $data['active_mode']=$this->config->get('payment_lunar_api_mode');

        if ($this->config->get('payment_lunar_api_mode') == 'live') {
            $data['lunar_public_key'] = $this->config->get('payment_lunar_public_key_live');
        } else {
            $data['lunar_public_key'] = $this->config->get('payment_lunar_public_key_test');
        }

        if ($this->config->get('payment_lunar_checkout_title') != '') {
            $data['popup_title'] = $this->config->get('payment_lunar_checkout_title');
        } else {
            $data['popup_title'] = $this->config->get('config_name');
        }

        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['lc']             = $this->session->data['language'];
        $data['mode']           = $this->config->get('payment_lunar_checkout_display_mode');

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

        $data['amount']        = $order_info['total'];
        $data['currency_code'] = $order_info['currency_code'];

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
        if ($this->config->get('payment_lunar_checkout_description') != '') {
            $data['popup_description'] = $this->config->get('payment_lunar_checkout_description');
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
        $this->logger = new Log('lunar.log');
        $log          = $this->config->get('payment_lunar_logging') ? true : false;

        $json = array();
        $ref  = $this->request->post['trans_ref'];

        if ($log) {
            $this->logger->write('************');
        }
        if ($log) {
            $this->logger->write('Transaction validation. Transaction refference: ' . $ref);
        }

        $app_key = $this->config->get('payment_lunar_api_mode') == 'live' ? $this->config->get('payment_lunar_app_key_live') : $this->config->get('payment_lunar_app_key_test');

        require_once(DIR_SYSTEM . 'library/Lunar/Client.php');

        Lunar\Client::setKey($app_key);

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $order_info['currency_code'] = strtoupper($order_info['currency_code']);

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
            if (isset($trans_data['transaction']['successful']) && (strtoUpper($trans_data['transaction']['currency']) == $order_info['currency_code']) && ($trans_data['transaction']['amount'] == $order_info['total'])) {
                $order_captured = false;

                if ($this->config->get('payment_lunar_capture_mode') == 'instant') {
                    $data         = array(
                        'amount'   => $order_info['total'],
                        'currency' => $order_info['currency_code']
                    );
                    $capture_data = Lunar\Transaction::capture($ref, $data);
                    if (! isset($capture_data['transaction'])) {
                        if ($log) {
                            $this->logger->write('Unable to capture amount of ' . $order_info['total'] . ' (' . $order_info['currency_code'] . '). Order #' . $order_info['order_id'] . ' history updated.');
                        }
                    } else {
                        if ($log) {
                            $this->logger->write('Transaction finished. Captured amount: ' . $order_info['total'] . ' (' . $order_info['currency_code'] . '). Order #' . $order_info['order_id'] . ' history updated.');
                        }
                        $order_captured = true;
                    }
                } else {
                    if ($log) {
                        $this->logger->write('Transaction authorized. Pending amount: ' . $order_info['total'] . ' (' . $order_info['currency_code'] . '). Order #' . $order_info['order_id'] . ' history updated.');
                    }
                }

                if (! $order_captured) {
                    $type                = 'Authorize';
                    $transaction_amount  = 0;
                    $total_amount        = 0;
                    $comment             = 'Lunar transaction: ref:' . $ref . "\r\n" . 'Authorized amount: ' . $order_info['total'] . ' (' . $order_info['currency_code'] . ')';
                    $new_order_status_id = $this->config->get('payment_lunar_authorize_status_id');
                    $json['success']     = $this->language->get('success_message_authorized');
                } else {
                    $type                = 'Capture';
                    $transaction_amount  = $order_info['total'];
                    $total_amount        = $order_info['total'];
                    $comment             = 'Lunar transaction: ref:' . $ref . "\r\n" . 'Captured amount: ' . $order_info['total'] . ' (' . $order_info['currency_code'] . ')';
                    $new_order_status_id = $this->config->get('payment_lunar_capture_status_id');
                    $json['success']     = $this->language->get('success_message_captured');
                }

                /** Insert new transaction. */
                $this->db->query("INSERT INTO `" . DB_PREFIX  . "lunar_transaction`
                                    SET order_id = '" . $order_info['order_id'] . "',
                                        transaction_id = '" . $ref . "',
                                        transaction_type = '" . $type . "',
                                        transaction_currency = '" . $order_info['currency_code'] . "',
                                        order_amount = '" . $order_info['total'] . "',
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
}
