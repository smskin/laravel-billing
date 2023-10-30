<?php

namespace SMSkin\Billing\Jobs;

use SMSkin\Billing\Controllers\IncreaseBalance;
use SMSkin\Billing\Events\EBalanceIncreaseCompleted;
use SMSkin\Billing\Events\EBalanceIncreaseFailed;
use SMSkin\Billing\Exceptions\AmountMustBeMoreThan0;
use SMSkin\Billing\Exceptions\NotUniqueOperationId;
use SMSkin\Billing\Requests\BalanceOperationRequest;

class IncreaseBalanceJob extends BillingJob
{
    public function __construct(protected BalanceOperationRequest $request)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        try {
            (new IncreaseBalance($this->request))->execute();
            $this->registerCompletedEvent();
        } catch (AmountMustBeMoreThan0|NotUniqueOperationId $exception) {
            $this->registerFailedEvent($exception);
        }
    }

    private function registerCompletedEvent()
    {
        event(new EBalanceIncreaseCompleted(
            $this->request->getOperationId(),
            $this->request->getTarget(),
            $this->request->getAmount()
        ));
    }

    private function registerFailedEvent(AmountMustBeMoreThan0|NotUniqueOperationId $exception)
    {
        event(new EBalanceIncreaseFailed(
            $this->request->getOperationId(),
            $this->request->getTarget(),
            $this->request->getAmount(),
            $exception
        ));
    }
}
