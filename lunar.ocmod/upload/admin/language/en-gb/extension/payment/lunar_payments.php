<?php

if (!defined('VENDOR_NAME_FIRST_UPPER')) define('VENDOR_NAME_FIRST_UPPER', 'Lunar');

// Heading
$_['heading_title'] = VENDOR_NAME_FIRST_UPPER . ' Payments';

// Text
$_['text_filter']                  = 'Filter';
$_['text_list']                    = 'Transactions List';
$_['text_no_results']              = 'No results!';
$_['entry_transaction_type']       = 'Transaction Type';
$_['entry_date_added']             = 'Date Added';
$_['text_setting_review_required'] = 'Please review and save ' . VENDOR_NAME_FIRST_UPPER . ' settings.';

// Popup textdomain()
$_['popup_title_transaction'] = 'New Transaction';
$_['popup_title_history']     = 'Transaction History';
$_['popup_description']       = 'Please review transaction data below before you Run transaction.';
$_['popup_transaction_id']    = 'Transaction ID:';
$_['popup_transaction_type']  = 'Action:';
$_['popup_amount']            = 'Amount';
$_['popup_close']             = "Close";
$_['popup_execute']           = "Execute";

// Column
$_['column_order_id']           = 'Order ID';
$_['column_transaction_id']     = 'Transaction ID';
$_['column_transaction_type']   = 'Type';
$_['column_transaction_amount'] = 'Transaction';
$_['column_order_amount']       = 'Order';
$_['column_total_amount']       = 'Balance';
$_['column_date_added']         = 'Date Added';
$_['column_action']             = 'Action';

// Button
$_['button_history'] = 'Order History';
$_['button_capture'] = 'Capture';
$_['button_refund']  = 'Refund';
$_['button_void']    = 'Void';

// Help

// Error
$_['error_permission']            = 'Error: You do not have permission to initiate ' . VENDOR_NAME_FIRST_UPPER . ' transaction!';
$_['error_transaction']           = 'Error: Invalid Transaction data!';
$_['error_amount_format']         = 'Error: Invalid Amount!';
$_['error_live_keys']             = 'Error: Invalid keys. Please review ' . VENDOR_NAME_FIRST_UPPER . ' settings!';
$_['error_test_keys']             = 'Error: Invalid Test keys. Please review ' . VENDOR_NAME_FIRST_UPPER . ' settings!';
$_['error_setup']                 = 'Error: ' . VENDOR_NAME_FIRST_UPPER . ' setup is not finished. Please review ' . VENDOR_NAME_FIRST_UPPER . ' settings!';
$_['error_transaction_currency']  = 'Error: Invalid transaction currency!';
$_['error_order_captured']        = 'Warning: Order already captured!';
$_['error_refund_before_capture'] = 'Warning: You need to Capture Order prior to Refund.';
$_['error_void_after_capture']    = 'Warning: You can\'t Void transaction now. It\'s already Captured, try to Refund.';
$_['error_message']               = 'Transaction Error!';

// Success
$_['success_transaction_capture'] = 'Successful Capture transaction. Captured %s';
$_['success_transaction_refund']  = 'Successful Refund transaction. Refunded %s';
$_['success_transaction_void']    = 'Successful Void transaction. Voided %s';
