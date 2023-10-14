<?php

require_once(DIR_SYSTEM . 'library/Lunar/model/AbstractLunarFrontModel.php');


class ModelExtensionPaymentLunarCard extends AbstractLunarFrontModel
{
    protected string $paymentMethodCode = 'card';

    protected string $paymentMethodConfigCode = 'payment_lunar_card';
}
