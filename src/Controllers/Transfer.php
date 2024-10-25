<?php

namespace SMSkin\Billing\Controllers;

use Illuminate\Database\UniqueConstraintViolationException;
use SMSkin\Billing\Actions\CreateTransferOperation;
use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Exceptions\AmountMustBeMoreThan0;
use SMSkin\Billing\Exceptions\InsufficientBalance;
use SMSkin\Billing\Exceptions\NotUniqueOperationId;
use SMSkin\Billing\Exceptions\RecipientIsSender;
use SMSkin\Billing\Models\Payment;
use SMSkin\LaravelSupport\Exceptions\MutexException;
use SMSkin\LaravelSupport\Traits\RedisMutexTrait;

class Transfer
{
    use RedisMutexTrait;

    public function __construct(
        private readonly string $operationId,
        private readonly Billingable $sender,
        private readonly Billingable $recipient,
        private readonly float $amount,
        private readonly string|null $description
    )
    {
    }


    /**
     * @throws MutexException
     * @throws AmountMustBeMoreThan0
     * @throws InsufficientBalance
     * @throws NotUniqueOperationId
     * @throws RecipientIsSender
     */
    public function execute(): void
    {
        $mutex = $this->createMutex($this->sender->getIdentityHash());
        try {
            $this->process();
        } finally {
            $mutex->unlock();
        }
    }


    /**
     * @throws NotUniqueOperationId
     * @throws AmountMustBeMoreThan0
     * @throws InsufficientBalance
     * @throws RecipientIsSender
     */
    private function process(): void
    {
        try {
            $this->checkAmount();
            $this->checkSubjects();
            $this->checkBalance();
            $this->createBillingOperation();
        } catch (UniqueConstraintViolationException $exception) {
            throw new NotUniqueOperationId($this->operationId, $exception);
        }
    }

    private function createBillingOperation(): void
    {
        (new CreateTransferOperation(
            $this->sender,
            collect([
                (new Payment())
                    ->setOperationId($this->operationId)
                    ->setRecipient($this->recipient)
                    ->setAmount($this->amount)
                    ->setDescription($this->description)
            ])
        ))->execute();
    }

    /**
     * @throws InsufficientBalance
     */
    private function checkBalance(): void
    {
        $balance = $this->sender->getBalance();
        if ($balance < $this->amount) {
            throw new InsufficientBalance($balance, $this->amount);
        }
    }

    /**
     * @throws RecipientIsSender
     */
    private function checkSubjects(): void
    {
        if ($this->sender->isEqual($this->recipient)) {
            throw new RecipientIsSender;
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
