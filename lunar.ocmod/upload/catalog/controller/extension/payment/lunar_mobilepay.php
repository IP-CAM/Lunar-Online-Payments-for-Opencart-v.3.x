<?php

require_once(DIR_SYSTEM . 'library/Lunar/controller/AbstractLunarFrontController.php');

/**
 * 
 */
class ControllerExtensionPaymentLunarMobilePay extends AbstractLunarFrontController
{
    const EXTENSION_PATH = 'extension/payment/lunar_mobilepay';

    public $paymentMethodCode = 'mobilePay';
}
