<?php

namespace SMSkin\Billing\Events;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Exceptions\AmountMustBeMoreThan0;
use SMSkin\Billing\Exceptions\NotUniqueOperationId;

class EBalanceIncreaseFailed extends EBalanceIncrease
{
    public function __construct(public string $operationId, public Billingable $target, public float $amount, public AmountMustBeMoreThan0|NotUniqueOperationId $exception)
    {
        parent::__construct($this->operationId, $this->target, $this->amount);
    }
}
