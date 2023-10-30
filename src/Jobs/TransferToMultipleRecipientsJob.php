<?php

namespace SMSkin\Billing\Jobs;

use SMSkin\Billing\Controllers\TransferToMultipleRecipients;
use SMSkin\Billing\Events\EBulkTransferCompleted;
use SMSkin\Billing\Events\EBulkTransferFailed;
use SMSkin\Billing\Exceptions\AmountMustBeMoreThan0;
use SMSkin\Billing\Exceptions\InsufficientBalance;
use SMSkin\Billing\Exceptions\NotUniqueOperationId;
use SMSkin\Billing\Exceptions\RecipientIsSender;
use SMSkin\Billing\Requests\TransferToMultipleRecipientsRequest;
use SMSkin\LaravelSupport\Exceptions\MutexException;

class TransferToMultipleRecipientsJob extends BillingJob
{
    public function __construct(protected TransferToMultipleRecipientsRequest $request)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        try {
            (new TransferToMultipleRecipients($this->request))->execute();
            $this->registerCompletedEvent();
        } catch (AmountMustBeMoreThan0|InsufficientBalance|NotUniqueOperationId|RecipientIsSender $exception) {
            $this->registerFailedEvent($exception);
        } catch (MutexException) {
            self::dispatch()->delay(now()->addSeconds(5));
        }
    }

    private function registerCompletedEvent()
    {
        event(new EBulkTransferCompleted(
            $this->request->getSender(),
            $this->request->getPayments()
        ));
    }

    private function registerFailedEvent(AmountMustBeMoreThan0|InsufficientBalance|RecipientIsSender|NotUniqueOperationId $exception)
    {
        event(new EBulkTransferFailed(
            $this->request->getSender(),
            $this->request->getPayments(),
            $exception
        ));
    }
}
