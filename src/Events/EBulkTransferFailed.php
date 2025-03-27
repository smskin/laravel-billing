<?php

namespace SMSkin\Billing\Events;

use Illuminate\Support\Collection;
use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Events\Enums\BulkTransferFailedReasonEnum;
use SMSkin\Billing\Models\Payment;

class EBulkTransferFailed extends EBulkTransfer
{
    /**
     * @param Billingable $sender
     * @param Collection<Payment> $payments
     * @param BulkTransferFailedReasonEnum $reason
     */
    public function __construct(public Billingable $sender, public Collection $payments, public BulkTransferFailedReasonEnum $reason)
    {
        parent::__construct($this->sender, $this->payments);
    }
}
