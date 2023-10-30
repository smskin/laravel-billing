<?php

namespace SMSkin\Billing\Events;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\LaravelSupport\BaseEvent;

abstract class EBalanceIncrease extends BaseEvent
{
    public function __construct(protected string $operationId, protected Billingable $target, protected float $amount)
    {
    }

    /**
     * @return string
     */
    public function getOperationId(): string
    {
        return $this->operationId;
    }
}