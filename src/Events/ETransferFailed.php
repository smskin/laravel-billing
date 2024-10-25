<?php

namespace SMSkin\Billing\Events;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Exceptions\AmountMustBeMoreThan0;
use SMSkin\Billing\Exceptions\InsufficientBalance;
use SMSkin\Billing\Exceptions\NotUniqueOperationId;
use SMSkin\Billing\Exceptions\RecipientIsSender;

class ETransferFailed extends ETransfer
{
    public function __construct(public string $operationId, public Billingable $sender, public Billingable $recipient, public float $amount, public InsufficientBalance|AmountMustBeMoreThan0|RecipientIsSender|NotUniqueOperationId $exception)
    {
        parent::__construct($this->operationId, $this->sender, $this->recipient, $this->amount);
    }
}
