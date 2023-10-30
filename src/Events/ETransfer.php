<?php

namespace SMSkin\Billing\Events;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\LaravelSupport\BaseEvent;

abstract class ETransfer extends BaseEvent
{
    public function __construct(protected string $operationId, protected Billingable $sender, protected Billingable $recipient, protected float $amount)
    {
    }

    /**
     * @return string
     */
    public function getOperationId(): string
    {
        return $this->operationId;
    }

    /**
     * @return Billingable
     */
    public function getSender(): Billingable
    {
        return $this->sender;
    }

    /**
     * @return Billingable
     */
    public function getRecipient(): Billingable
    {
        return $this->recipient;
    }
}
