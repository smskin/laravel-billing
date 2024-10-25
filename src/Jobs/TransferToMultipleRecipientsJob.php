<?php

namespace SMSkin\Billing\Jobs;

use Illuminate\Support\Collection;
use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Controllers\TransferToMultipleRecipients;
use SMSkin\Billing\Events\EBulkTransferCompleted;
use SMSkin\Billing\Events\EBulkTransferFailed;
use SMSkin\Billing\Exceptions\AmountMustBeMoreThan0;
use SMSkin\Billing\Exceptions\InsufficientBalance;
use SMSkin\Billing\Exceptions\NotUniqueOperationId;
use SMSkin\Billing\Exceptions\RecipientIsSender;
use SMSkin\Billing\Models\Payment;
use SMSkin\LaravelSupport\Exceptions\MutexException;

class TransferToMultipleRecipientsJob extends BillingJob
{
    /**
     * @param Billingable $sender
     * @param Collection<Payment> $payments
     */
    public function __construct(
        public readonly Billingable $sender,
        public readonly Collection $payments
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        try {
            (new TransferToMultipleRecipients($this->sender, $this->payments))->execute();
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
            $this->sender,
            $this->payments
        ));
    }

    private function registerFailedEvent(AmountMustBeMoreThan0|InsufficientBalance|RecipientIsSender|NotUniqueOperationId $exception)
    {
        event(new EBulkTransferFailed(
            $this->sender,
            $this->payments,
            $exception
        ));
    }
}
