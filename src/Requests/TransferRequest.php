<?php

namespace SMSkin\Billing\Requests;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Traits\RoundTrait;

class TransferRequest
{
    use RoundTrait;

    protected string $operationId;
    protected Billingable $sender;
    protected Billingable $recipient;
    protected float $amount;
    protected string|null $description = null;

    /**
     * @param string $operationId
     * @return TransferRequest
     */
    public function setOperationId(string $operationId): self
    {
        $this->operationId = $operationId;
        return $this;
    }

    /**
     * @return string
     */
    public function getOperationId(): string
    {
        return $this->operationId;
    }

    /**
     * @param Billingable $sender
     * @return TransferRequest
     */
    public function setSender(Billingable $sender): self
    {
        $this->sender = $sender;
        return $this;
    }

    /**
     * @return Billingable
     */
    public function getSender(): Billingable
    {
        return $this->sender;
    }

    /**
     * @param Billingable $recipient
     * @return TransferRequest
     */
    public function setRecipient(Billingable $recipient): self
    {
        $this->recipient = $recipient;
        return $this;
    }

    /**
     * @return Billingable
     */
    public function getRecipient(): Billingable
    {
        return $this->recipient;
    }

    /**
     * @param float $amount
     * @return TransferRequest
     */
    public function setAmount(float $amount): self
    {
        $this->amount = $this->round($amount);
        return $this;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param string|null $description
     * @return TransferRequest
     */
    public function setDescription(string|null $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): string|null
    {
        return $this->description;
    }
}
