<?php

namespace SMSkin\Billing\Events;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Exceptions\AmountMustBeMoreThan0;
use SMSkin\Billing\Exceptions\NotUniqueOperationId;

class EBalanceIncreaseFailed extends EBalanceIncrease
{
    public function __construct(protected string $operationId, protected Billingable $target, protected float $amount, protected AmountMustBeMoreThan0|NotUniqueOperationId $exception)
    {
        parent::__construct($this->operationId, $this->target, $this->amount);
    }

    /**
     * @return AmountMustBeMoreThan0|NotUniqueOperationId
     */
    public function getException(): AmountMustBeMoreThan0|NotUniqueOperationId
    {
        return $this->exception;
    }

    /**
     * @return string
     */
    public function getOperationId(): string
    {
        return $this->operationId;
    }
}
