<?php

require_once(DIR_SYSTEM . 'library/Lunar/model/AbstractLunarFrontModel.php');
require_once(DIR_SYSTEM . 'library/Lunar/helper/LunarHelper.php');


class ModelExtensionPaymentLunarMobilePay extends AbstractLunarFrontModel
{
    protected string $paymentMethodCode = LunarHelper::LUNAR_MOBILEPAY_CODE;
    protected string $paymentMethodConfigCode = LunarHelper::LUNAR_MOBILEPAY_CONFIG_CODE;
}
