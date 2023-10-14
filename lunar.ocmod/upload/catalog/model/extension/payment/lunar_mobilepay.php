<?php

require_once(DIR_SYSTEM . 'library/Lunar/model/AbstractLunarFrontModel.php');


class ModelExtensionPaymentLunarMobilePay extends AbstractLunarFrontModel
{
    protected string $paymentMethodCode = 'mobilePay';

    protected string $paymentMethodConfigCode = 'payment_lunar_mobilepay';
}
