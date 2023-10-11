<?php

require_once(DIR_SYSTEM . 'library/Lunar/Client.php');
require_once(DIR_SYSTEM . 'library/Lunar/Transaction.php');

use Lunar\Client;
use Lunar\Transaction;

class ControllerExtensionPaymentLunarTransaction extends Controller
{
    const EXTENSION_PATH = 'extension/payment/lunar';
    const THIS_MODEL_PATH = 'extension/payment/lunar_transaction';

    public function index()
    {
    }


    /*******************************************************************************
     ********************* LOGIC USED IN ADMIN BACKEND  ****************************
     *******************************************************************************/

    /**
     * Do Transaction On Order Status Change
     *
     * (here is the place that the event 'catalog/controller/api/order/history/after' can reach this function)
     *
     */
    public function doTransactionOnOrderStatusChange(&$route, &$args)
    {
        /** Load checkout order model. */
        $this->load->model('checkout/order');

        /** Load language */
        $this->load->language(self::EXTENSION_PATH);

        /**
         * Check if order ID is present and extract order by ID.
         *
         */
        if (isset($_GET['order_id']) && $order = $this->model_checkout_order->getOrder($_GET['order_id'])) {

            if ('lunar' != $order['payment_code']) {
                return;
            }

            /**
             * Extract last transaction for current order.
             * - load transaction model
             * - get last transaction
             */
            $this->load->model(self::THIS_MODEL_PATH);
            $lastTransaction = $this->model_extension_payment_lunar_transaction->getLastTransaction($order['order_id']);

            /**
             * Create new Std object to be used in execute() method bellow.
             */
            $this->orderData = new StdClass();

            $this->orderData->order_store_id = $order['store_id'];
            $this->orderData->order_id = $order['order_id'];
            $this->orderData->ref = $lastTransaction['transaction_id'];
            $this->orderData->amount = $order['total'];

            /** Check type of transaction. */
            if ($order['order_status_id'] === $this->config->get('payment_lunar_capture_status_id')) {
                $this->orderData->type = 'Capture';

            } elseif ($order['order_status_id'] === $this->config->get('payment_lunar_refund_status_id')) {
                $this->orderData->type = 'Refund';

            } elseif ($order['order_status_id'] === $this->config->get('payment_lunar_cancel_status_id')) {
                $this->orderData->type = 'Cancel';

            } else {
                /** Return that no other status is available for transaction. */
                return;
            }

            /**
             * Make the transaction.
             */
            $this->transaction();
        }
    }

    /**
     * Make a transaction.
     */
    public function transaction()
    {
        $json = $this->transaction_validate();
        if (! $json) {
            $json = $this->execute();
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Execute the transaction.
     */
    public function execute()
    {
        $this->load->language(self::EXTENSION_PATH);

        $this->load->model(self::THIS_MODEL_PATH);

        $this->logger = new Log('lunar.log');
        $log = $this->config->get('payment_lunar_logging') ? true : false;

        $orderStoreId = $this->orderData->order_store_id;
        $orderId      = $this->orderData->order_id;
        $ref          = $this->orderData->ref;
        $type         = $this->orderData->type;
        $input_amount = $this->orderData->amount;

        $history = $this->model_extension_payment_lunar_transaction->getLastTransaction($orderId);
        $history['transaction_currency'] = strtoupper($history['transaction_currency']);

        if (is_null($history)) {
            $json['error'] = $this->language->get('error_transaction_error_returned');

            return $json;
        }

        $formattedAmount = $this->getFormattedAmount($input_amount, $history['transaction_currency']);

        if ($log) {
            $this->logger->write('*****************************');
            $this->logger->write('Admin transaction ' . $type . ' for order: ' . $history['order_id'] . ' (' . $formattedAmount['formatted'] . ' ' . $history['transaction_currency'] . ')');
            $this->logger->write('Transaction Reference: ' . $ref);
        }


        $pluginSettingsData = $this->getSettingsData($orderStoreId);
        $app_key = $pluginSettingsData['payment_lunar_api_mode'] == 'live' ?
                    $pluginSettingsData['payment_lunar_app_key_live'] :
                    $pluginSettingsData['payment_lunar_app_key_test'];

        Client::setKey($app_key);
        $trans_data = Transaction::fetch($ref);

        if (is_null($trans_data)) {
            $this->logger->write('Invalid transaction data. Unable to Fetch transaction.');
            $json['error'] = $this->language->get('error_message');

            return $json;
        }

        if ($trans_data['transaction']['currency'] != $history['transaction_currency']) {
            if ($log) {
                $this->logger->write('Error: Capture currency (' . $history['transaction_currency'] . ') not equal to Transaction currency (' . $trans_data['transaction']['currency'] . '). Transaction aborted!');
            }
            $json['error'] = $this->language->get('error_transaction_currency');
            ;

            return $json;
        }

        $response = array();

        /** Verify which transaction type is. */
        switch ($type) {

            case "Capture":
                if ($trans_data['transaction']['pendingAmount'] == 0) {
                    if ($log) {
                        $this->logger->write('Error: There is no Pending amount. Order is already captured. Transaction aborted.');
                    }
                    $json['error'] = $this->language->get('error_order_captured');
                    ;

                    return $json;
                }

                if ($trans_data['transaction']['pendingAmount'] < $formattedAmount['in_minor']) {
                    if ($log) {
                        $this->logger->write('Warning: Capture amount is large than Transaction pending amount. Pending amount will be captured.');
                    }
                    $formattedAmount = $this->getFormattedAmount($trans_data['transaction']['pendingAmount'], $history['transaction_currency'], true);
                }

                $data = array(
                    'amount'     => $formattedAmount['in_minor'],
                    'descriptor' => '',
                    'currency'   => $history['transaction_currency']
                );

                /** CAPTURE the order amount. */
                $response = Transaction::capture($ref, $data);

                break;

            case "Refund":
                if ($trans_data['transaction']['capturedAmount'] == 0) {
                    if ($log) {
                        $this->logger->write('Error: There is no Captured amount. Order is not captured and cannot be refunded. Transaction aborted.');
                    }
                    $json['error'] = $this->language->get('error_refund_before_capture');
                    ;

                    return $json;
                }
                if ($trans_data['transaction']['capturedAmount'] < $formattedAmount['in_minor']) {
                    if ($log) {
                        $this->logger->write('Warning: Refund amount is larger than Transaction captured amount. Captured amount will be refunded.');
                    }
                    $formattedAmount = $this->getFormattedAmount($trans_data['transaction']['capturedAmount'], $history['transaction_currency'], true);
                }
                $data = array(
                    'amount'     => $formattedAmount['in_minor'],
                    'descriptor' => ''
                );

                /** REFUND the order amount. */
                $response = Transaction::refund($ref, $data);

                break;

            case "Cancel":
                if ($trans_data['transaction']['capturedAmount'] > 0) {
                    if ($log) {
                        $this->logger->write('Error: Order already Captured and cannot be Cancelled. Transaction aborted.');
                    }
                    $json['error'] = $this->language->get('error_cancel_after_capture');
                    ;

                    return $json;
                }
                if ($trans_data['transaction']['pendingAmount'] < $formattedAmount['in_minor']) {
                    if ($log) {
                        $this->logger->write('Warning: Cancel amount is larger than Transaction captured amount. Captured amount will be cancelled.');
                    }
                    $formattedAmount = $this->getFormattedAmount($trans_data['transaction']['pendingAmount'], $history['transaction_currency'], true);
                }

                $data = array(
                    'amount' => $formattedAmount['in_minor'],
                );

                /** CANCEL the order amount. */
                $response = Transaction::void($ref, $data);
                break;
        }

        if (isset($response['transaction'])) {
            $data = array(
                'order_id'             => $history['order_id'],
                'transaction_id'       => $ref,
                'transaction_type'     => $type,
                'transaction_currency' => $history['transaction_currency'],
                'order_amount'         => $history['order_amount'],
                'transaction_amount'   => $formattedAmount['formatted'],
                'history'              => '0',
                'date_added'           => 'NOW()'
            );

            /** Add transaction. */
            $this->model_extension_payment_lunar_transaction->addTransaction($data);

            $new_order_status_id = $this->config->get('payment_lunar_' . strtolower($type) . '_status_id');

            /** Update order history. */
            $this->model_extension_payment_lunar_transaction->updateOrder($data, $new_order_status_id);

            $json['success'] = sprintf($this->language->get('success_transaction_' . strtolower($type)), $formattedAmount['formatted']) . ' ' . $history['transaction_currency'];

            return $json;

        } else {
            $error = array();
            foreach ($response as $field_error) {
                $error[] = ucwords($field_error['field']) . ': ' . $field_error['message'];
            }
            $error_message = implode(" ", $error);
            $json['error'] = $this->language->get('error_transaction_error_returned') . ' ' . $error_message;

            return $json;
        }
    }

    /**
     * Validate the transaction inputs.
     */
    protected function transaction_validate()
    {
        $json = array();
        $this->load->language(self::EXTENSION_PATH);

        if (is_null($this->orderData->ref) || is_null($this->orderData->type) || is_null($this->orderData->amount)) {
            $json['error'] = $this->language->get('error_transaction');

            return $json;
        }

        if (! is_numeric($this->orderData->amount)) {
            $json['error'] = $this->language->get('error_amount_format');

            return $json;
        }
    }

    /**
     * Get plugin store specific settings.
     */
    private function getSettingsData($storeId)
    {
        $this->load->model('setting/setting');
        return $this->model_setting_setting->getSetting('payment_lunar', $storeId);
    }

    /**
     * Get formatted amount
     */
    private function getFormattedAmount($amount, $currency_code, $isMinor = false)
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
        $exponent_three = array( 'BHD', 'IQD', 'JOD', 'KWD', 'OMR', 'TND' );
        $exponent       = 2;
        if (in_array($currency_code, $exponent_zero)) {
            $exponent = 0;
        } elseif (in_array($currency_code, $exponent_three)) {
            $exponent = 3;
        }

        $multiplier = pow(10, $exponent);
        $formattedAmount     = array();

        $symbol_left  = $this->currency->getSymbolLeft($currency_code);
        $symbol_right = $this->currency->getSymbolRight($currency_code);

        if ($isMinor) {
            $formattedAmount['string'] = (string) ( $amount );
        } else {
            $formattedAmount['string'] = (string) ( $amount * $multiplier );
        }
        $formattedAmount['in_minor']           = (int) $formattedAmount['string'];
        $formattedAmount['converted'] = $formattedAmount['in_minor'] / $multiplier;
        $formattedAmount['formatted'] = $symbol_left . number_format($formattedAmount['converted'], $exponent, $this->language->get('decimal_point'), $this->language->get('thousand_point')) . $symbol_right;

        return $formattedAmount;
    }
}
