<?php

namespace SMSkin\Billing\Jobs;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Controllers\IncreaseBalance;
use SMSkin\Billing\Events\EBalanceIncreaseCompleted;
use SMSkin\Billing\Events\EBalanceIncreaseFailed;
use SMSkin\Billing\Exceptions\AmountMustBeMoreThan0;
use SMSkin\Billing\Exceptions\NotUniqueOperationId;

class IncreaseBalanceJob extends BillingJob
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
            (new IncreaseBalance($this->operationId, $this->target, $this->amount, $this->description))->execute();
            $this->registerCompletedEvent();
        } catch (AmountMustBeMoreThan0|NotUniqueOperationId $exception) {
            $this->registerFailedEvent($exception);
        }
    }

    private function registerCompletedEvent()
    {
        event(new EBalanceIncreaseCompleted(
            $this->operationId,
            $this->target,
            $this->amount
        ));
    }

    private function registerFailedEvent(AmountMustBeMoreThan0|NotUniqueOperationId $exception)
    {
        event(new EBalanceIncreaseFailed(
            $this->operationId,
            $this->target,
            $this->amount,
            $exception
        ));
    }
}
