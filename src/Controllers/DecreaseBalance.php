<?php

namespace SMSkin\Billing\Controllers;

use SMSkin\Billing\Actions\CreateDecreaseBalanceOperation;
use SMSkin\Billing\Exceptions\AmountMustBeMoreThan0;
use SMSkin\Billing\Exceptions\InsufficientBalance;
use SMSkin\Billing\Exceptions\NotUniqueOperationId;
use SMSkin\Billing\Requests\BalanceOperationRequest;
use Illuminate\Database\UniqueConstraintViolationException;
use SMSkin\LaravelSupport\Exceptions\MutexException;
use SMSkin\LaravelSupport\Traits\RedisMutexTrait;

class DecreaseBalance
{
    use RedisMutexTrait;

    public function __construct(protected BalanceOperationRequest $request)
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
        $mutex = $this->createMutex($this->request->getTarget()->getIdentityHash());
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
            throw new NotUniqueOperationId($this->request->getOperationId(), $exception);
        }
    }

    private function createBillingOperation(): void
    {
        (new CreateDecreaseBalanceOperation($this->request))->execute();
    }

    /**
     * @throws InsufficientBalance
     */
    private function checkBalance(): void
    {
        $balance = $this->request->getTarget()->getBalance();
        $amount = $this->request->getAmount();
        if ($balance < $amount) {
            throw new InsufficientBalance($balance, $amount);
        }
    }

    /**
     * @throws AmountMustBeMoreThan0
     */
    private function checkAmount(): void
    {
        $amount = $this->request->getAmount();
        if ($amount <= 0) {
            throw new AmountMustBeMoreThan0($amount);
        }
    }
}
