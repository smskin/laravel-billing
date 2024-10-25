<?php

namespace SMSkin\Billing\Controllers;

use SMSkin\Billing\Actions\CreateDecreaseBalanceOperation;
use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Exceptions\AmountMustBeMoreThan0;
use SMSkin\Billing\Exceptions\InsufficientBalance;
use SMSkin\Billing\Exceptions\NotUniqueOperationId;
use Illuminate\Database\UniqueConstraintViolationException;
use SMSkin\LaravelSupport\Exceptions\MutexException;
use SMSkin\LaravelSupport\Traits\RedisMutexTrait;

class DecreaseBalance
{
    use RedisMutexTrait;

    public function __construct(
        private readonly string $operationId,
        private readonly Billingable $target,
        private readonly float $amount,
        private readonly string|null $description
    )
    {
    }

    /**
     * @throws MutexException
     * @throws AmountMustBeMoreThan0
     * @throws InsufficientBalance
     * @throws UniqueConstraintViolationException
     * @throws NotUniqueOperationId
     */
    public function execute(): void
    {
        $mutex = $this->createMutex($this->target->getIdentityHash());
        try {
            $this->process();
        } finally {
            $mutex->unlock();
        }
    }

    /**
     * @throws AmountMustBeMoreThan0
     * @throws InsufficientBalance
     * @throws UniqueConstraintViolationException
     * @throws NotUniqueOperationId
     */
    private function process(): void
    {
        try {
            $this->checkAmount();
            $this->checkBalance();
            $this->createBillingOperation();
        } catch (UniqueConstraintViolationException $exception) {
            throw new NotUniqueOperationId($this->operationId, $exception);
        }
    }

    private function createBillingOperation(): void
    {
        (new CreateDecreaseBalanceOperation(
            $this->operationId,
            $this->target,
            $this->amount,
            $this->description
        ))->execute();
    }

    /**
     * @throws InsufficientBalance
     */
    private function checkBalance(): void
    {
        $balance = $this->target->getBalance();
        if ($balance < $this->amount) {
            throw new InsufficientBalance($balance, $this->amount);
        }
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
