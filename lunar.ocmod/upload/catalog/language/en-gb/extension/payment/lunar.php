<?php

$_['text_title'] = 'Credit Card';

// Success
$_['success_message_authorized'] = 'Order authorization successful!';
$_['success_message_captured']   = 'Order successfully paid!';
$_['success_transaction_capture'] = 'Successful Capture transaction. Captured %s';
$_['success_transaction_refund']  = 'Successful Refund transaction. Refunded %s';
$_['success_transaction_void']    = 'Successful Void transaction. Voided %s';

// Error
$_['error_no_transaction_found']       = 'No transaction reference found';
$_['error_invalid_transaction_data']   = 'Invalid transaction';
$_['error_transaction_error_returned'] = 'Transaction error';
$_['error_empty_transaction_result']   = 'Invalid transaction';
$_['error_transaction']                = 'Error: Invalid Transaction data!';
$_['error_amount_format']              = 'Error: Invalid Amount!';
$_['error_setup']                      = 'Error: Lunar setup is not finished. Please review Lunar settings!';
$_['error_transaction_currency']       = 'Error: Invalid transaction currency!';
$_['error_order_captured']             = 'Warning: Order already captured!';
$_['error_refund_before_capture']      = 'Warning: You need to Capture Order prior to Refund.';
$_['error_void_after_capture']         = 'Warning: You can\'t Void transaction now. It\'s already Captured, try to Refund.';
$_['error_message']                    = 'Transaction Error!';

// Warning
$_['warning_test_mode'] = 'TEST MODE ENABLED. In test mode, you can use the card number 4100 0000 0000 0000 with any CVC and a valid expiration date.';
