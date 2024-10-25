<?php

namespace SMSkin\Billing;

use Illuminate\Support\Collection;
use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Controllers\CreateOperationsReport;
use SMSkin\Billing\Controllers\DecreaseBalance;
use SMSkin\Billing\Controllers\GetBalances;
use SMSkin\Billing\Controllers\IncreaseBalance;
use SMSkin\Billing\Controllers\Transfer;
use SMSkin\Billing\Controllers\TransferToMultipleRecipients;
use SMSkin\Billing\Jobs\DecreaseBalanceJob;
use SMSkin\Billing\Jobs\IncreaseBalanceJob;
use SMSkin\Billing\Jobs\TransferJob;
use SMSkin\Billing\Jobs\TransferToMultipleRecipientsJob;
use SMSkin\Billing\Models\Balance;
use SMSkin\Billing\Models\Payment;
use SMSkin\Billing\Models\Report;
use SMSkin\LaravelSupport\Exceptions\MutexException;

class Billing
{
    /**
     * @return Collection<Balance>
     * @var Collection<Billingable>
     */
    public function getBalances(Collection $accounts): Collection
    {
        return (new GetBalances($accounts))->execute();
    }

    /**
     * @throws Exceptions\NotUniqueOperationId
     * @throws Exceptions\AmountMustBeMoreThan0
     */
    public function increaseBalance(string $operationId, Billingable $target, float $amount, string|null $description = null): void
    {
        (new IncreaseBalance($operationId, $target, $amount, $description))->execute();
    }

    public function increaseBalanceAsync(string $operationId, Billingable $target, float $amount, string|null $description = null): void
    {
        dispatch(new IncreaseBalanceJob($operationId, $target, $amount, $description));
    }

    /**
     * @throws MutexException
     * @throws Exceptions\NotUniqueOperationId
     * @throws Exceptions\AmountMustBeMoreThan0
     * @throws Exceptions\InsufficientBalance
     */
    public function decreaseBalance(string $operationId, Billingable $target, float $amount, string|null $description = null): void
    {
        (new DecreaseBalance($operationId, $target, $amount, $description))->execute();
    }

    public function decreaseBalanceAsync(string $operationId, Billingable $target, float $amount, string|null $description = null): void
    {
        dispatch(new DecreaseBalanceJob($operationId, $target, $amount, $description));
    }

    /**
     * @throws MutexException
     * @throws Exceptions\NotUniqueOperationId
     * @throws Exceptions\AmountMustBeMoreThan0
     * @throws Exceptions\InsufficientBalance
     * @throws Exceptions\RecipientIsSender
     */
    public function transfer(string $operationId, Billingable $sender, Billingable $recipient, float $amount, string|null $description = null): void
    {
        (new Transfer($operationId, $sender, $recipient, $amount, $description))->execute();
    }

    public function transferAsync(string $operationId, Billingable $sender, Billingable $recipient, float $amount, string|null $description = null): void
    {
        dispatch((new TransferJob($operationId, $sender, $recipient, $amount, $description)));
    }

    /**
     * @param Billingable $sender
     * @param Collection<Payment> $payments
     * @throws Exceptions\AmountMustBeMoreThan0
     * @throws Exceptions\InsufficientBalance
     * @throws Exceptions\NotUniqueOperationId
     * @throws Exceptions\RecipientIsSender
     * @throws MutexException
     */
    public function transferToMultipleRecipients(Billingable $sender, Collection $payments): void
    {
        (new TransferToMultipleRecipients($sender, $payments))->execute();
    }

    /**
     * @param Billingable $sender
     * @param Collection<Payment> $payments
     */
    public function transferToMultipleRecipientsAsync(Billingable $sender, Collection $payments): void
    {
        dispatch(new TransferToMultipleRecipientsJob($sender, $payments));
    }

    public function createOperationsReport($page, $perPage): Report
    {
        return (new CreateOperationsReport($page, $perPage))->execute();
    }
}
