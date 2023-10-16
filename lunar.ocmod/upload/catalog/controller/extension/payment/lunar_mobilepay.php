<?php

require_once(DIR_SYSTEM . 'library/Lunar/controller/AbstractLunarFrontController.php');
require_once(DIR_SYSTEM . 'library/Lunar/helper/LunarHelper.php');

/**
 * 
 */
class ControllerExtensionPaymentLunarMobilePay extends AbstractLunarFrontController
{
    protected string $paymentMethodCode = LunarHelper::LUNAR_MOBILEPAY_CODE;
    protected string $paymentMethodConfigCode = LunarHelper::LUNAR_MOBILEPAY_CONFIG_CODE;
}
