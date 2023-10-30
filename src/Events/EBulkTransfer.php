<?php

namespace SMSkin\Billing\Events;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Requests\Models\Payment;
use Illuminate\Support\Collection;
use SMSkin\LaravelSupport\BaseEvent;

abstract class EBulkTransfer extends BaseEvent
{
    /**
     * @param Billingable $sender
     * @param Collection<Payment> $payments
     */
    public function __construct(protected Billingable $sender, protected Collection $payments)
    {

    }
}
