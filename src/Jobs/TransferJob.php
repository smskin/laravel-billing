<?php

namespace SMSkin\Billing\Jobs;

use SMSkin\Billing\Controllers\Transfer;
use SMSkin\Billing\Events\ETransferCompleted;
use SMSkin\Billing\Events\ETransferFailed;
use SMSkin\Billing\Exceptions\AmountMustBeMoreThan0;
use SMSkin\Billing\Exceptions\InsufficientBalance;
use SMSkin\Billing\Exceptions\NotUniqueOperationId;
use SMSkin\Billing\Exceptions\RecipientIsSender;
use SMSkin\Billing\Requests\TransferRequest;
use SMSkin\LaravelSupport\Exceptions\MutexException;

class TransferJob extends BillingJob
{
    public function __construct(protected TransferRequest $request)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        try {
            (new Transfer($this->request))->execute();
            $this->registerCompletedEvent();
        } catch (AmountMustBeMoreThan0|InsufficientBalance|NotUniqueOperationId|RecipientIsSender $exception) {
            $this->registerFailedEvent($exception);
        } catch (MutexException) {
            self::dispatch()->delay(now()->addSeconds(5));
        }
    }

    private function registerCompletedEvent()
    {
        event(new ETransferCompleted(
            $this->request->getOperationId(),
            $this->request->getSender(),
            $this->request->getRecipient(),
            $this->request->getAmount(),
        ));
    }

    private function registerFailedEvent(AmountMustBeMoreThan0|InsufficientBalance|RecipientIsSender|NotUniqueOperationId $exception)
    {
        event(new ETransferFailed(
            $this->request->getOperationId(),
            $this->request->getSender(),
            $this->request->getRecipient(),
            $this->request->getAmount(),
            $exception
        ));
    }
}
