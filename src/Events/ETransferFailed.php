<?php

namespace SMSkin\Billing\Events;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Events\Enums\TransferFailedReasonEnum;

class ETransferFailed extends ETransfer
{
    public function __construct(public string $operationId, public Billingable $sender, public Billingable $recipient, public float $amount, public TransferFailedReasonEnum $reason)
    {
        parent::__construct($this->operationId, $this->sender, $this->recipient, $this->amount);
    }
}
