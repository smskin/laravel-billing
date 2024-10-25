<?php

namespace SMSkin\Billing\Controllers;

use Illuminate\Database\UniqueConstraintViolationException;
use SMSkin\Billing\Actions\CreateIncreaseBalanceOperation;
use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Exceptions\AmountMustBeMoreThan0;
use SMSkin\Billing\Exceptions\NotUniqueOperationId;

class IncreaseBalance
{
    public function __construct(
        private readonly string $operationId,
        private readonly Billingable $target,
        private readonly float $amount,
        private readonly string|null $description
    ) {
    }

    /**
     * @throws NotUniqueOperationId
     * @throws AmountMustBeMoreThan0
     */
    public function execute(): void
    {
        try {
            $this->checkAmount();
            $this->createBillingOperation();
        } catch (UniqueConstraintViolationException $exception) {
            throw new NotUniqueOperationId($this->operationId, $exception);
        }
    }

    private function createBillingOperation(): void
    {
        (new CreateIncreaseBalanceOperation(
            $this->operationId,
            $this->target,
            $this->amount,
            $this->description
        ))->execute();
    }

    /**
     * @throws AmountMustBeMoreThan0
     */
    private function checkAmount(): void
    {
        if ($this->amount <= 0) {
            throw new AmountMustBeMoreThan0($this->amount);
        }
    }
}
