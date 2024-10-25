<?php

namespace SMSkin\Billing\Events;

use Illuminate\Support\Collection;
use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Models\Payment;
use SMSkin\LaravelSupport\BaseEvent;

abstract class EBulkTransfer extends BaseEvent
{
    /**
     * @param Billingable $sender
     * @param Collection<Payment> $payments
     */
    public function __construct(public Billingable $sender, public Collection $payments)
    {

    }
}
