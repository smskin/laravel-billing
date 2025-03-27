<?php

namespace SMSkin\Billing\Jobs;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Controllers\DecreaseBalance;
use SMSkin\Billing\Events\EDecreaseBalanceCompleted;
use SMSkin\Billing\Events\EDecreaseBalanceFailed;
use SMSkin\Billing\Events\Enums\BalanceDecreaseFailedReasonEnum;
use SMSkin\Billing\Exceptions\AmountMustBeMoreThan0;
use SMSkin\Billing\Exceptions\InsufficientBalance;
use SMSkin\Billing\Exceptions\NotUniqueOperationId;
use SMSkin\LaravelSupport\Exceptions\MutexException;

class DecreaseBalanceJob extends BillingJob
{
    public function __construct(
        public readonly string $operationId,
        public readonly Billingable $target,
        public readonly float $amount,
        public readonly string|null $description
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        try {
            (new DecreaseBalance($this->operationId, $this->target, $this->amount, $this->description))->execute();

            event(new EDecreaseBalanceCompleted(
                $this->operationId,
                $this->target,
                $this->amount,
                $this->description
            ));
        } catch (InsufficientBalance|AmountMustBeMoreThan0|NotUniqueOperationId $exception) {
            $this->registerFailedEvent($exception);
        } catch (MutexException) {
            self::dispatch()->delay(now()->addSeconds(5));
        }
    }

    private function registerFailedEvent(NotUniqueOperationId|InsufficientBalance|AmountMustBeMoreThan0 $exception)
    {
        event(new EDecreaseBalanceFailed(
            $this->operationId,
            $this->target,
            $this->amount,
            $this->description,
            match (true) {
                $exception instanceof AmountMustBeMoreThan0 => BalanceDecreaseFailedReasonEnum::AMOUNT_MUST_BE_MORE_THAN_0,
                $exception instanceof NotUniqueOperationId => BalanceDecreaseFailedReasonEnum::NOT_UNIQUE_OPERATION_ID,
                $exception instanceof InsufficientBalance => BalanceDecreaseFailedReasonEnum::INSUFFICIENT_BALANCE
            }
        ));
    }
}
