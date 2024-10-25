<?php

namespace SMSkin\Billing\Models;

use Carbon\Carbon;
use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Enums\OperationEnum;

class ReportOperation
{
    protected string $operationId;

    protected OperationEnum $operation;

    protected Billingable|null $sender;

    protected Billingable|null $recipient;

    protected float $amount;

    protected string|null $description;

    protected Carbon $createdAt;

    /**
     * @param string $operationId
     * @return ReportOperation
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
     * @param OperationEnum $operation
     * @return ReportOperation
     */
    public function setOperation(OperationEnum $operation): self
    {
        $this->operation = $operation;
        return $this;
    }

    /**
     * @return OperationEnum
     */
    public function getOperation(): OperationEnum
    {
        return $this->operation;
    }

    /**
     * @param Billingable|null $sender
     * @return ReportOperation
     */
    public function setSender(Billingable|null $sender): self
    {
        $this->sender = $sender;
        return $this;
    }

    /**
     * @return Billingable|null
     */
    public function getSender(): Billingable|null
    {
        return $this->sender;
    }

    /**
     * @param Billingable|null $recipient
     * @return ReportOperation
     */
    public function setRecipient(Billingable|null $recipient): self
    {
        $this->recipient = $recipient;
        return $this;
    }

    /**
     * @return Billingable|null
     */
    public function getRecipient(): Billingable|null
    {
        return $this->recipient;
    }

    /**
     * @param float $amount
     * @return ReportOperation
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
     * @param string|null $description
     * @return ReportOperation
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

    /**
     * @param Carbon $createdAt
     * @return ReportOperation
     */
    public function setCreatedAt(Carbon $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return Carbon
     */
    public function getCreatedAt(): Carbon
    {
        return $this->createdAt;
    }
}
