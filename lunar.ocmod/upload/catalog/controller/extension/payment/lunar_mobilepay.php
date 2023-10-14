<?php

require_once(DIR_SYSTEM . 'library/Lunar/controller/AbstractLunarFrontController.php');

/**
 * 
 */
class ControllerExtensionPaymentLunarMobilePay extends AbstractLunarFrontController
{
    const EXTENSION_PATH = 'extension/payment/lunar_mobilepay';

    protected string $paymentMethodCode = 'mobilePay';

    protected string $paymentMethodConfigCode = 'payment_lunar_mobilepay';
}
