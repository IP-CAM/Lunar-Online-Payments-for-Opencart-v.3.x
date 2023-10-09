<?php

require_once(DIR_SYSTEM . 'library/Lunar/controller/AbstractLunarAdminController.php');

/**
 * 
 */
class ControllerExtensionPaymentLunarMobilePay extends AbstractLunarAdminController
{
    const EXTENSION_PATH = 'extension/payment/lunar_mobilepay';

    public string $paymentMethodCode = 'mobilePay';
    
    public string $paymentMethodConfigCode = 'payment_lunar_mobilePay';
}
