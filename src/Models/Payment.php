<?php

namespace SMSkin\Billing\Models;

use SMSkin\Billing\Contracts\Billingable;

class Payment
{
    protected string $operationId;
    protected Billingable $recipient;
    protected float $amount;
    protected string|null $description = null;

    /**
     * @param Billingable $recipient
     * @return Payment
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
     * @return Payment
     */
    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
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
     * @param string $operationId
     * @return Payment
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
     * @param string|null $description
     * @return Payment
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
