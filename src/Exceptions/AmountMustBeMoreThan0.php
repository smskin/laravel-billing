<?php

namespace SMSkin\Billing\Exceptions;

class AmountMustBeMoreThan0 extends BillingException
{
    public function __construct(protected float $amount)
    {
        parent::__construct();
    }
}
