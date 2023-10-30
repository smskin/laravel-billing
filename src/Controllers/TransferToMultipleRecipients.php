<?php

namespace SMSkin\Billing\Controllers;

use SMSkin\Billing\Actions\CreateTransferOperation;
use SMSkin\Billing\Exceptions\AmountMustBeMoreThan0;
use SMSkin\Billing\Exceptions\InsufficientBalance;
use SMSkin\Billing\Exceptions\NotUniqueOperationId;
use SMSkin\Billing\Exceptions\RecipientIsSender;
use SMSkin\Billing\Requests\TransferToMultipleRecipientsRequest;
use SMSkin\Billing\Requests\CreateTransferOperationRequest;
use SMSkin\Billing\Requests\Models\Payment;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;
use SMSkin\LaravelSupport\Exceptions\MutexException;
use SMSkin\LaravelSupport\Traits\RedisMutexTrait;

class TransferToMultipleRecipients
{
    use RedisMutexTrait;

    public function __construct(protected TransferToMultipleRecipientsRequest $request)
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
        $mutex = $this->createMutex($this->request->getSender()->getIdentityHash());
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
        $balance = $this->request->getSender()->getBalance();
        if ($balance < $amount) {
            throw new InsufficientBalance($balance, $amount);
        }
    }

    private function createBillingOperations(): void
    {
        (new CreateTransferOperation(
            (new CreateTransferOperationRequest)
                ->setSender($this->request->getSender())
                ->setPayments($this->request->getPayments())
        ))->execute();
    }

    /**
     * @throws RecipientIsSender
     */
    private function checkSubjects(): void
    {
        $sender = $this->request->getSender();
        $this->request->getPayments()->each(static function (Payment $payment) use ($sender) {
            if ($sender->isEqual($payment->getRecipient())) {
                throw new RecipientIsSender;
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

        $this->request->getPayments()->each(static function (Payment $payment) {
            $amount = $payment->getAmount();
            if ($amount <= 0) {
                throw new AmountMustBeMoreThan0($amount);
            }
        });
    }

    private function getTotalAmount(): float
    {
        return $this->request->getPayments()->sum(static function (Payment $payment) {
            return $payment->getAmount();
        });
    }
}
