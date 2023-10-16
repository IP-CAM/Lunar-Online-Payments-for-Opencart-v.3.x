<?php

require_once(DIR_SYSTEM . 'library/Lunar/model/AbstractLunarFrontModel.php');
require_once(DIR_SYSTEM . 'library/Lunar/helper/LunarHelper.php');


class ModelExtensionPaymentLunarCard extends AbstractLunarFrontModel
{
    protected string $paymentMethodCode = LunarHelper::LUNAR_CARD_CODE;
    protected string $paymentMethodConfigCode = LunarHelper::LUNAR_CARD_CONFIG_CODE;
}
