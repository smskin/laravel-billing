<?php

namespace SMSkin\Billing\Events;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Exceptions\AmountMustBeMoreThan0;
use SMSkin\Billing\Exceptions\InsufficientBalance;
use SMSkin\Billing\Exceptions\NotUniqueOperationId;

class EDecreaseBalanceFailed extends EDecreaseBalance
{
    public function __construct(protected string $operationId, protected Billingable $target, protected float $amount, protected InsufficientBalance|AmountMustBeMoreThan0|NotUniqueOperationId $exception)
    {
        parent::__construct($this->operationId, $this->target, $this->amount);
    }

    /**
     * @return AmountMustBeMoreThan0|InsufficientBalance|NotUniqueOperationId
     */
    public function getException(): AmountMustBeMoreThan0|InsufficientBalance|NotUniqueOperationId
    {
        return $this->exception;
    }
}
