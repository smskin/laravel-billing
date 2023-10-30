<?php

namespace SMSkin\Billing\Controllers;

use SMSkin\Billing\Actions\CreateTransferOperation;
use SMSkin\Billing\Exceptions\AmountMustBeMoreThan0;
use SMSkin\Billing\Exceptions\InsufficientBalance;
use SMSkin\Billing\Exceptions\NotUniqueOperationId;
use SMSkin\Billing\Exceptions\RecipientIsSender;
use SMSkin\Billing\Requests\CreateTransferOperationRequest;
use SMSkin\Billing\Requests\Models\Payment;
use SMSkin\Billing\Requests\TransferRequest;
use Illuminate\Database\UniqueConstraintViolationException;
use SMSkin\LaravelSupport\Exceptions\MutexException;
use SMSkin\LaravelSupport\Traits\RedisMutexTrait;

class Transfer
{
    use RedisMutexTrait;

    public function __construct(protected TransferRequest $request)
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
            $this->checkAmount();
            $this->checkSubjects();
            $this->checkBalance();
            $this->createBillingOperation();
        } catch (UniqueConstraintViolationException $exception) {
            throw new NotUniqueOperationId($this->request->getOperationId(), $exception);
        }
    }

    private function createBillingOperation(): void
    {
        (new CreateTransferOperation(
            (new CreateTransferOperationRequest)
                ->setSender($this->request->getSender())
                ->setPayments(collect([
                    (new Payment())
                        ->setOperationId($this->request->getOperationId())
                        ->setRecipient($this->request->getRecipient())
                        ->setAmount($this->request->getAmount())
                        ->setDescription($this->request->getDescription())
                ]))
        ))->execute();
    }

    /**
     * @throws InsufficientBalance
     */
    private function checkBalance(): void
    {
        $balance = $this->request->getSender()->getBalance();
        $amount = $this->request->getAmount();
        if ($balance < $amount) {
            throw new InsufficientBalance($balance, $amount);
        }
    }

    /**
     * @throws RecipientIsSender
     */
    private function checkSubjects(): void
    {
        if ($this->request->getSender()->isEqual($this->request->getRecipient())) {
            throw new RecipientIsSender;
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
