<?php

if (!defined('VENDOR_NAME')) define('VENDOR_NAME', 'lunar');
if (!defined('VENDOR_NAME_FIRST_UPPER')) define('VENDOR_NAME_FIRST_UPPER', 'Lunar');
if (!defined('VENDOR_WEBSITE')) define('VENDOR_WEBSITE', 'https://lunar.app');

$_['heading_title'] = VENDOR_NAME_FIRST_UPPER;
$_['text_' . VENDOR_NAME]  = '<a target="_BLANK" href="' . VENDOR_WEBSITE . '"><img src="view/image/payment/' . VENDOR_NAME . '.png" alt="' . VENDOR_NAME_FIRST_UPPER . '" title="' . VENDOR_NAME_FIRST_UPPER . '" /></a>';

// Text
$_['text_description']             = VENDOR_NAME_FIRST_UPPER . ' enables you to accept credit and debit cards on your OpenCart platform. If you don\'t already have an account with ' . VENDOR_NAME_FIRST_UPPER . ', you can create it at ' . VENDOR_WEBSITE;
$_['text_extension']               = 'Extensions';
$_['text_edit_settings']           = 'Edit ' . VENDOR_NAME_FIRST_UPPER;
$_['text_module_version']          = '1.0.0';
$_['text_general_settings']        = 'General';
$_['text_advanced_settings']       = 'Advanced';
$_['text_capture_instant']         = 'Instant';
$_['text_capture_delayed']         = 'Delayed';
$_['text_display_mode_popup']      = 'Pop-up Window';
$_['text_display_mode_inline']     = 'Inline Form';
$_['text_success']                 = 'Success: You have modified ' . VENDOR_NAME_FIRST_UPPER . ' settings!';
$_['text_upgrade']                 = VENDOR_NAME_FIRST_UPPER . ' successfully upgraded.';
$_['text_setting_review_required'] = 'Please review and save ' . VENDOR_NAME_FIRST_UPPER . ' settings.';
$_['text_test']                    = 'Test';
$_['text_live']                    = 'Live';

//Default
$_['default_payment_method_title']             = 'Credit Card';
$_['default_payment_' . VENDOR_NAME . '_checkout_cc_logo'] = array(
    'mastercard.png',
    'maestro.png',
    'visa.png',
    'visaelectron.png'
);

//Buttons
$_['button_payments'] = VENDOR_NAME_FIRST_UPPER . ' Payments';

// Entry
$_['entry_payment_method_title']  = 'Payment method title';
$_['entry_checkout_popup_title']  = 'Popup popup title';
$_['entry_checkout_display_mode'] = 'Display Mode';
$_['entry_checkout_cc_logo']      = 'Payment method credit card logos';
$_['entry_public_key_test']       = 'Test mode Public Key';
$_['entry_app_key_test']          = 'Test mode App Key';
$_['entry_public_key_live']       = 'Public Key';
$_['entry_app_key_live']          = 'App Key';
$_['entry_capture_mode']          = 'Capture mode';
$_['entry_authorize_status_id']   = 'Authorized Status';
$_['entry_capture_status_id']     = 'Captured Status';
$_['entry_refund_status_id']      = 'Refunded Status';
$_['entry_void_status_id']        = 'Voided Status';
$_['entry_api_mode']              = 'Transaction mode';
$_['entry_payment_enabled']       = 'Status';
$_['entry_logging']               = 'Debug Logging';
$_['entry_minimum_total']         = 'Total';
$_['entry_geo_zone']              = 'Geo Zone';
$_['entry_sort_order']            = 'Sort Order';
$_['entry_store']                 = 'Stores';
$_['select_store']                = 'Select store';

// Help
$_['help_payment_method_title']       = 'Payment method title displayed to the customer.';
$_['help_checkout_popup_title']       = 'The text shown in the popup where the customer inserts the card details. Leave blank to use store name.';
$_['help_checkout_popup_description'] = 'The text shown in the popup where the customer inserts the card details. Leave blank to use ordered product names.';
$_['help_checkout_display_mode']      = 'Choose how payment form to be displayed to customer.';
$_['help_checkout_cc_logo']           = 'Payment method credit card logos displayed on checkout page.';
$_['help_public_key_test']            = 'Get it from your ' . VENDOR_NAME_FIRST_UPPER . ' dashboard';
$_['help_app_key_test']               = 'Get it from your ' . VENDOR_NAME_FIRST_UPPER . ' dashboard';
$_['help_public_key_live']            = 'Get it from your ' . VENDOR_NAME_FIRST_UPPER . ' dashboard';
$_['help_app_key_live']               = 'Get it from your ' . VENDOR_NAME_FIRST_UPPER . ' dashboard';
$_['help_capture_mode']               = 'If you deliver your product instantly (e.g. a digital product), choose Instant mode. If not, use Delayed. In Delayed mode to capture a transaction you can use the transaction list from the transactions page. The transaction page can be accessed by clicking the green button located at the top of the payment settings page, to the right of the save and cancel buttons.';
$_['help_authorize_status_id']        = 'Set the default order status when an payment is Authorized.';
$_['help_capture_status_id']          = 'Set the default order status when an payment is Captured.';
$_['help_refund_status_id']           = 'Set the default order status when an payment is Refunded.';
$_['help_void_status_id']             = 'Set the default order status when an payment is VOided.';
$_['help_api_mode']                   = 'In test mode, you can create a successful transaction with the card number 4100 0000 0000 0000 with any CVC and a valid expiration date.';
$_['help_payment_enabled']            = 'Set ' . VENDOR_NAME_FIRST_UPPER . ' payment status.';
$_['help_logging']                    = 'Logs transaction related information to the ' . VENDOR_NAME_FIRST_UPPER . ' log.';
$_['help_minimum_total']              = 'The checkout total the order must reach before ' . VENDOR_NAME_FIRST_UPPER . ' payment becomes active';
$_['help_geo_zone']                   = 'Limit ' . VENDOR_NAME_FIRST_UPPER . ' payment method to be available to chosen geo zone only.';
$_['help_sort_order']                 = 'Set sort order to control how payments are displayed in checkout available payment methods.';
$_['help_store']                      = 'Select which stores can use ' . VENDOR_NAME_FIRST_UPPER . ' payment method.';
$_['help_select_store']               = 'Select the store for which we will make the settings.';

// Error
$_['error_permission']           = 'Warning: You do not have permission to modify ' . VENDOR_NAME_FIRST_UPPER . ' payment!';
$_['error_warning']              = 'Warning: Please check the form carefully for errors!';
$_['error_payment_method_title'] = 'Payment Method Title Required!';

$_['error_app_key']              = 'The App Key is required!';
$_['error_public_key']           = 'The Public Key is required!';
$_['error_app_key_invalid']      = 'The App Key doesn\'t seem to be valid!';
$_['error_public_key_invalid']   = 'The Public Key doesn\'t seem to be valid!';
$_['error_app_key_invalid_mode'] = 'The App Key is not valid or set to %s mode!';
