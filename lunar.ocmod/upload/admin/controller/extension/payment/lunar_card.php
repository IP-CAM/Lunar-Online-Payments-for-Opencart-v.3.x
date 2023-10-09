<?php

require_once(DIR_SYSTEM . 'library/Lunar/controller/AbstractLunarAdminController.php');

/**
 * 
 */
class ControllerExtensionPaymentLunarCard extends AbstractLunarAdminController
{
    const EXTENSION_PATH = 'extension/payment/lunar_card';

    public string $paymentMethodCode = 'card';
}
