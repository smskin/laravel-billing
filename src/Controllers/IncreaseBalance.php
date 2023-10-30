<?php

namespace SMSkin\Billing\Controllers;

use SMSkin\Billing\Actions\CreateIncreaseBalanceOperation;
use SMSkin\Billing\Exceptions\AmountMustBeMoreThan0;
use SMSkin\Billing\Exceptions\NotUniqueOperationId;
use SMSkin\Billing\Requests\BalanceOperationRequest;
use Illuminate\Database\UniqueConstraintViolationException;

class IncreaseBalance
{
    public function __construct(protected BalanceOperationRequest $request)
    {
    }

    /**
     * @throws NotUniqueOperationId
     * @throws AmountMustBeMoreThan0
     */
    public function execute(): void
    {
        try {
            $this->checkAmount();
            $this->createBillingOperation();
        } catch (UniqueConstraintViolationException $exception) {
            throw new NotUniqueOperationId($this->request->getOperationId(), $exception);
        }
    }

    private function createBillingOperation(): void
    {
        (new CreateIncreaseBalanceOperation($this->request))->execute();
    }

    /**
     * @throws AmountMustBeMoreThan0
     */
    private function checkAmount(): void
    {
        $amount = $this->request->getAmount();
        if ($amount <= 0) {
            throw new AmountMustBeMoreThan0($amount);
        }
    }
}
