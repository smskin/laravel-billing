<?php

namespace SMSkin\Billing\Events;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\LaravelSupport\BaseEvent;

abstract class EBalanceIncrease extends BaseEvent
{
    public function __construct(public string $operationId, public Billingable $target, public float $amount)
    {
    }
}
