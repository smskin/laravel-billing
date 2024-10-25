<?php

namespace SMSkin\Billing\Controllers;

use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SMSkin\Billing\Actions\CreateTransferOperation;
use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Exceptions\AmountMustBeMoreThan0;
use SMSkin\Billing\Exceptions\InsufficientBalance;
use SMSkin\Billing\Exceptions\NotUniqueOperationId;
use SMSkin\Billing\Exceptions\RecipientIsSender;
use SMSkin\Billing\Models\Payment;
use SMSkin\LaravelSupport\Exceptions\MutexException;
use SMSkin\LaravelSupport\Traits\RedisMutexTrait;

class TransferToMultipleRecipients
{
    use RedisMutexTrait;

    /**
     * @param Billingable $sender
     * @param Collection<Payment> $payments
     */
    public function __construct(
        private readonly Billingable $sender,
        private readonly Collection $payments
    ) {
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
            $totalAmount = $this->getTotalAmount();
            $this->checkAmount($totalAmount);
            $this->checkSubjects();
            $this->checkBalance($totalAmount);
            $this->createBillingOperations();
        } catch (UniqueConstraintViolationException $exception) {
            $operationId = Str::match('/Duplicate entry \'(\S+)\'/i', $exception->errorInfo[2]);
            throw new NotUniqueOperationId($operationId, $exception);
        }
    }

    /**
     * @throws InsufficientBalance
     */
    private function checkBalance(float $amount): void
    {
        $balance = $this->sender->getBalance();
        if ($balance < $amount) {
            throw new InsufficientBalance($balance, $amount);
        }
    }

    private function createBillingOperations(): void
    {
        (new CreateTransferOperation($this->sender, $this->payments))->execute();
    }

    /**
     * @throws RecipientIsSender
     */
    private function checkSubjects(): void
    {
        $this->payments->each(function (Payment $payment) {
            if ($this->sender->isEqual($payment->getRecipient())) {
                throw new RecipientIsSender();
            }
        });
    }

    /**
     * @throws AmountMustBeMoreThan0
     */
    private function checkAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw new AmountMustBeMoreThan0($amount);
        }

        $this->payments->each(static function (Payment $payment) {
            $amount = $payment->getAmount();
            if ($amount <= 0) {
                throw new AmountMustBeMoreThan0($amount);
            }
        });
    }

    private function getTotalAmount(): float
    {
        return $this->payments->sum(static function (Payment $payment) {
            return $payment->getAmount();
        });
    }
}
