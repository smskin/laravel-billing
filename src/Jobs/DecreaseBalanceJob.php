<?php

namespace SMSkin\Billing\Jobs;

use Illuminate\Database\UniqueConstraintViolationException;
use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Controllers\DecreaseBalance;
use SMSkin\Billing\Events\EDecreaseBalanceCompleted;
use SMSkin\Billing\Events\EDecreaseBalanceFailed;
use SMSkin\Billing\Exceptions\AmountMustBeMoreThan0;
use SMSkin\Billing\Exceptions\InsufficientBalance;
use SMSkin\Billing\Exceptions\NotUniqueOperationId;
use SMSkin\LaravelSupport\Exceptions\MutexException;

class DecreaseBalanceJob extends BillingJob
{
    public function __construct(
        private readonly string $operationId,
        private readonly Billingable $target,
        private readonly float $amount,
        private readonly string|null $description
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
                $this->amount
            ));
        } catch (InsufficientBalance|AmountMustBeMoreThan0|UniqueConstraintViolationException|NotUniqueOperationId $exception) {
            event(new EDecreaseBalanceFailed(
                $this->operationId,
                $this->target,
                $this->amount,
                $exception
            ));
        } catch (MutexException) {
            self::dispatch()->delay(now()->addSeconds(5));
        }
    }
}
