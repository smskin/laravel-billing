<?php

namespace SMSkin\Billing\Jobs;

use SMSkin\Billing\Controllers\DecreaseBalance;
use SMSkin\Billing\Events\EDecreaseBalanceCompleted;
use SMSkin\Billing\Events\EDecreaseBalanceFailed;
use SMSkin\Billing\Exceptions\AmountMustBeMoreThan0;
use SMSkin\Billing\Exceptions\InsufficientBalance;
use SMSkin\Billing\Exceptions\NotUniqueOperationId;
use SMSkin\Billing\Requests\BalanceOperationRequest;
use Illuminate\Database\UniqueConstraintViolationException;
use SMSkin\LaravelSupport\Exceptions\MutexException;

class DecreaseBalanceJob extends BillingJob
{
    public function __construct(protected BalanceOperationRequest $request)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        try {
            (new DecreaseBalance($this->request))->execute();

            event(new EDecreaseBalanceCompleted(
                $this->request->getOperationId(),
                $this->request->getTarget(),
                $this->request->getAmount()
            ));
        } catch (InsufficientBalance|AmountMustBeMoreThan0|UniqueConstraintViolationException|NotUniqueOperationId $exception) {
            event(new EDecreaseBalanceFailed(
                $this->request->getOperationId(),
                $this->request->getTarget(),
                $this->request->getAmount(),
                $exception
            ));
        } catch (MutexException) {
            self::dispatch()->delay(now()->addSeconds(5));
        }
    }
}
