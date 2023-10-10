<?php

// $_['heading_title'] = 'Lunar';
$_['text_lunar']  = '<a target="_BLANK" href="https://lunar.app"><img height=40 src="view/image/payment/lunar.png" alt="Lunar logo" title="Lunar logo" /></a>';

// Text
$_['text_description']             = 'Lunar enables you to accept credit and debit cards on your OpenCart platform. If you don\'t already have an account with Lunar, you can create it at <a target="_blank" href="https://lunar.app"><b>https://lunar.app</b></a>';
$_['text_extension']               = 'Extensions';

$_['text_general_settings']        = 'General';
$_['text_advanced_settings']       = 'Advanced';
$_['text_capture_instant']         = 'Instant';
$_['text_capture_delayed']         = 'Delayed';
$_['text_success']                 = 'Success: You have modified Lunar settings!';
$_['text_upgrade']                 = 'Lunar successfully upgraded.';
$_['text_setting_review_required'] = 'Please review and save Lunar settings.';
$_['text_test']                    = 'Test';
$_['text_live']                    = 'Live';

//Default
$_['default_payment_lunar_checkout_cc_logo'] = array(
    'mastercard.png',
    'maestro.png',
    'visa.png',
    'visaelectron.png'
);

$_['select_store']                = 'Select store';

// Entry
$_['entry_payment_enabled']       = 'Status';
$_['entry_api_mode']              = 'Transaction mode';
$_['entry_public_key_test']       = 'Test mode Public Key';
$_['entry_app_key_test']          = 'Test mode App Key';
$_['entry_public_key_live']       = 'Public Key';
$_['entry_app_key_live']          = 'App Key';
$_['entry_capture_mode']          = 'Capture mode';
$_['entry_logo_url']              = 'Logo URL';
$_['entry_configuration_id']      = 'Configuration ID';
$_['entry_method_title']          = 'Payment method title';
$_['entry_shop_title']            = 'Shop title';
$_['entry_checkout_cc_logo']      = 'Payment method credit card logos';

$_['entry_authorize_status_id']   = 'Authorized Status';
$_['entry_capture_status_id']     = 'Captured Status';
$_['entry_refund_status_id']      = 'Refunded Status';
$_['entry_cancel_status_id']      = 'Cancelled Status';
$_['entry_logging']               = 'Debug Logging';
$_['entry_minimum_total']         = 'Total';
$_['entry_geo_zone']              = 'Geo Zone';
$_['entry_sort_order']            = 'Sort Order';
$_['entry_store']                 = 'Stores';

// Help
$_['help_api_mode']                   = 'In test mode, you can create a successful transaction with the card number 4100 0000 0000 0000 with any CVC and a valid expiration date.';
$_['help_public_key_test']            = 'Get it from your Lunar dashboard';
$_['help_app_key_test']               = 'Get it from your Lunar dashboard';
$_['help_public_key_live']            = 'Get it from your Lunar dashboard';
$_['help_app_key_live']               = 'Get it from your Lunar dashboard';
$_['help_capture_mode']               = 'If you deliver your product instantly (e.g. a digital product), choose Instant mode. If not, use Delayed. In Delayed mode to capture a transaction you can change the order status to that set in Advanced section (default: Complete).';
$_['help_logo_url']                   = 'The logo used in hosted checkout page after redirect';
$_['help_configuration_id']           = 'Get it from your Lunar dashboard. It must have exactly 32 chars';
$_['help_method_title']               = 'Payment method title displayed to the customer.';
$_['help_shop_title']                 = 'The text shown in the hosted checkout page where the customer inserts the card details. Leave blank to use store name.';
$_['help_checkout_description']       = 'The text shown in the checkout page near payment method name.';
$_['help_checkout_cc_logo']           = 'Payment method card logos displayed on checkout page.';

$_['help_authorize_status_id']        = 'Set the default order status when an payment is Authorized.';
$_['help_capture_status_id']          = 'Set the default order status when an payment is Captured.';
$_['help_refund_status_id']           = 'Set the default order status when an payment is Refunded.';
$_['help_cancel_status_id']           = 'Set the default order status when an payment is Cancelled.';
$_['help_payment_enabled']            = 'Set Lunar payment status.';
$_['help_logging']                    = 'Logs transaction related information to the Lunar log.';
$_['help_minimum_total']              = 'The checkout total the order must reach before Lunar payment becomes active';
$_['help_geo_zone']                   = 'Limit Lunar payment method to be available to chosen geo zone only.';
$_['help_sort_order']                 = 'Set sort order to control how payments are displayed in checkout available payment methods.';
$_['help_select_store']               = 'Select the store for which we will save the settings.';

// Error
$_['error_permission']           = 'Warning: You do not have permission to modify Lunar payment!';
$_['error_warning']              = 'Warning: Please check the form carefully for errors!';
$_['error_method_title']         = 'Payment Method Title Required!';

$_['error_app_key']              = 'The App Key is required!';
$_['error_public_key']           = 'The Public Key is required!';
$_['error_app_key_invalid']      = 'The App Key doesn\'t seem to be valid!';
$_['error_public_key_invalid']   = 'The Public Key doesn\'t seem to be valid!';
$_['error_app_key_invalid_mode'] = 'The App Key is not valid or checkout mode is incorrect!';
$_['error_config_id']            = 'The Configuration ID is required';
$_['error_config_id_len']        = 'The Configuration ID should have exactly 32 characters. Current count: %s';
$_['error_logo_url_required']    = 'The Logo URL is required';
$_['error_logo_url_https']       = 'The Logo URL must start with "https://"';
$_['error_logo_url_invalid']     = 'The Logo URL is not valid.';
