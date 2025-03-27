<?php

namespace SMSkin\Billing\Jobs;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Controllers\Transfer;
use SMSkin\Billing\Events\Enums\TransferFailedReasonEnum;
use SMSkin\Billing\Events\ETransferCompleted;
use SMSkin\Billing\Events\ETransferFailed;
use SMSkin\Billing\Exceptions\AmountMustBeMoreThan0;
use SMSkin\Billing\Exceptions\InsufficientBalance;
use SMSkin\Billing\Exceptions\NotUniqueOperationId;
use SMSkin\Billing\Exceptions\RecipientIsSender;
use SMSkin\LaravelSupport\Exceptions\MutexException;

class TransferJob extends BillingJob
{
    public function __construct(
        public readonly string $operationId,
        public readonly Billingable $sender,
        public readonly Billingable $recipient,
        public readonly float $amount,
        public readonly bool $allowCredit,
        public readonly string|null $description
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        try {
            (new Transfer(
                $this->operationId,
                $this->sender,
                $this->recipient,
                $this->amount,
                $this->allowCredit,
                $this->description
            ))->execute();
            $this->registerCompletedEvent();
        } catch (AmountMustBeMoreThan0|InsufficientBalance|NotUniqueOperationId|RecipientIsSender $exception) {
            $this->registerFailedEvent($exception);
        } catch (MutexException) {
            dispatch(new self($this->operationId, $this->sender, $this->recipient, $this->amount, $this->allowCredit, $this->description))->delay(now()->addSeconds(5));
        }
    }

    private function registerCompletedEvent()
    {
        event(new ETransferCompleted(
            $this->operationId,
            $this->sender,
            $this->recipient,
            $this->amount,
            $this->description
        ));
    }

    private function registerFailedEvent(AmountMustBeMoreThan0|InsufficientBalance|RecipientIsSender|NotUniqueOperationId $exception)
    {
        event(new ETransferFailed(
            $this->operationId,
            $this->sender,
            $this->recipient,
            $this->amount,
            $this->description,
            match (true) {
                $exception instanceof AmountMustBeMoreThan0 => TransferFailedReasonEnum::AMOUNT_MUST_BE_MORE_THAN_0,
                $exception instanceof NotUniqueOperationId => TransferFailedReasonEnum::NOT_UNIQUE_OPERATION_ID,
                $exception instanceof InsufficientBalance => TransferFailedReasonEnum::INSUFFICIENT_BALANCE,
                $exception instanceof RecipientIsSender => TransferFailedReasonEnum::RECIPIENT_IS_SENDER,
            }
        ));
    }

    public function tags(): array
    {
        return array_unique([
            $this->operationId,
            $this->sender->getBillingSubsystem(),
            $this->sender->getBillingType(),
            $this->sender->getBillingId(),
            $this->recipient->getBillingSubsystem(),
            $this->recipient->getBillingType(),
            $this->recipient->getBillingId(),
        ]);
    }
}
