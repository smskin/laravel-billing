<?php

namespace SMSkin\Billing\Requests;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Traits\RoundTrait;

class BalanceOperationRequest
{
    use RoundTrait;

    protected string $operationId;
    protected Billingable $target;
    protected float $amount;
    protected string|null $description = null;

    /**
     * @param Billingable $target
     * @return BalanceOperationRequest
     */
    public function setTarget(Billingable $target): self
    {
        $this->target = $target;
        return $this;
    }

    /**
     * @return Billingable
     */
    public function getTarget(): Billingable
    {
        return $this->target;
    }

    /**
     * @param float $amount
     * @return BalanceOperationRequest
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
     * @param string $operationId
     * @return BalanceOperationRequest
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
     * @return BalanceOperationRequest
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
