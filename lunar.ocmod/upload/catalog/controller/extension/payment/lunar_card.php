<?php

require_once(DIR_SYSTEM . 'library/Lunar/controller/AbstractLunarFrontController.php');

/**
 * 
 */
class ControllerExtensionPaymentLunarCard extends AbstractLunarFrontController
{
    const EXTENSION_PATH = 'extension/payment/lunar_card';

    public $paymentMethodCode = 'card';
}
