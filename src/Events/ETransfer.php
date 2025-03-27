<?php

namespace SMSkin\Billing\Events;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\LaravelSupport\BaseEvent;

abstract class ETransfer extends BaseEvent
{
    public function __construct(public string $operationId, public Billingable $sender, public Billingable $recipient, public float $amount, public string|null $description)
    {
    }
}
