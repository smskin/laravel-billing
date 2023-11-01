<?php

namespace SMSkin\Billing\Events;

use SMSkin\Billing\Contracts\Billingable;

abstract class EDecreaseBalance
{
    public function __construct(public string $operationId, public Billingable $target, public float $amount)
    {
    }
}
