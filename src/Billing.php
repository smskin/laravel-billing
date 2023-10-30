<?php

namespace SMSkin\Billing;

use Illuminate\Support\Collection;
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
use SMSkin\Billing\Models\Report;
use SMSkin\Billing\Requests\CreateOperationsReportRequest;
use SMSkin\Billing\Requests\GetBalancesRequest;
use SMSkin\Billing\Requests\BalanceOperationRequest;
use SMSkin\Billing\Requests\TransferRequest;
use SMSkin\Billing\Requests\TransferToMultipleRecipientsRequest;
use SMSkin\LaravelSupport\Exceptions\MutexException;

class Billing
{
    /**
     * @param GetBalancesRequest $request
     * @return Collection<Balance>
     */
    public function getBalances(GetBalancesRequest $request): Collection
    {
        return (new GetBalances($request))->execute();
    }

    /**
     * @throws Exceptions\NotUniqueOperationId
     * @throws Exceptions\AmountMustBeMoreThan0
     */
    public function increaseBalance(BalanceOperationRequest $request): void
    {
        (new IncreaseBalance($request))->execute();
    }

    public function increaseBalanceAsync(BalanceOperationRequest $request): void
    {
        dispatch(new IncreaseBalanceJob($request));
    }

    /**
     * @throws MutexException
     * @throws Exceptions\NotUniqueOperationId
     * @throws Exceptions\AmountMustBeMoreThan0
     * @throws Exceptions\InsufficientBalance
     */
    public function decreaseBalance(BalanceOperationRequest $request): void
    {
        (new DecreaseBalance($request))->execute();
    }

    public function decreaseBalanceAsync(BalanceOperationRequest $request): void
    {
        dispatch(new DecreaseBalanceJob($request));
    }

    /**
     * @throws MutexException
     * @throws Exceptions\NotUniqueOperationId
     * @throws Exceptions\AmountMustBeMoreThan0
     * @throws Exceptions\InsufficientBalance
     * @throws Exceptions\RecipientIsSender
     */
    public function transfer(TransferRequest $request): void
    {
        (new Transfer($request))->execute();
    }

    public function transferAsync(TransferRequest $request): void
    {
        dispatch((new TransferJob($request)));
    }

    /**
     * @throws MutexException
     * @throws Exceptions\NotUniqueOperationId
     * @throws Exceptions\AmountMustBeMoreThan0
     * @throws Exceptions\InsufficientBalance
     * @throws Exceptions\RecipientIsSender
     */
    public function transferToMultipleRecipients(TransferToMultipleRecipientsRequest $request): void
    {
        (new TransferToMultipleRecipients($request))->execute();
    }

    public function transferToMultipleRecipientsAsync(TransferToMultipleRecipientsRequest $request): void
    {
        dispatch(new TransferToMultipleRecipientsJob($request));
    }

    public function createOperationsReport(CreateOperationsReportRequest $request): Report
    {
        return (new CreateOperationsReport($request))->execute();
    }
}