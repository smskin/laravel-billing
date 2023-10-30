<?php

namespace SMSkin\Billing\Events;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Exceptions\AmountMustBeMoreThan0;
use SMSkin\Billing\Exceptions\InsufficientBalance;
use SMSkin\Billing\Exceptions\NotUniqueOperationId;
use SMSkin\Billing\Exceptions\RecipientIsSender;

class ETransferFailed extends ETransfer
{
    public function __construct(protected string $operationId, protected Billingable $sender, protected Billingable $recipient, protected float $amount, protected InsufficientBalance|AmountMustBeMoreThan0|RecipientIsSender|NotUniqueOperationId $exception)
    {
        parent::__construct($this->operationId, $this->sender,$this->recipient, $this->amount);
    }

    /**
     * @return AmountMustBeMoreThan0|InsufficientBalance|NotUniqueOperationId|RecipientIsSender
     */
    public function getException(): AmountMustBeMoreThan0|InsufficientBalance|RecipientIsSender|NotUniqueOperationId
    {
        return $this->exception;
    }
}
