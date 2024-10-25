<?php

namespace SMSkin\Billing\Events;

use Illuminate\Support\Collection;
use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Exceptions\AmountMustBeMoreThan0;
use SMSkin\Billing\Exceptions\InsufficientBalance;
use SMSkin\Billing\Exceptions\NotUniqueOperationId;
use SMSkin\Billing\Exceptions\RecipientIsSender;
use SMSkin\Billing\Models\Payment;

class EBulkTransferFailed extends EBulkTransfer
{
    /**
     * @param Billingable $sender
     * @param Collection<Payment> $payments
     * @param InsufficientBalance|AmountMustBeMoreThan0|RecipientIsSender|NotUniqueOperationId $exception
     */
    public function __construct(public Billingable $sender, public Collection $payments, public InsufficientBalance|AmountMustBeMoreThan0|RecipientIsSender|NotUniqueOperationId $exception)
    {
        parent::__construct($this->sender, $this->payments);
    }
}
