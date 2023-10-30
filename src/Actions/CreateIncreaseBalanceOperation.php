<?php

namespace SMSkin\Billing\Actions;

use SMSkin\Billing\Database\BillingOperation;
use SMSkin\Billing\Enums\OperationEnum;
use SMSkin\Billing\Requests\BalanceOperationRequest;

class CreateIncreaseBalanceOperation
{
    public function __construct(protected BalanceOperationRequest $request)
    {
    }

    public function execute(): void
    {
        $target = $this->request->getTarget();

        BillingOperation::query()->insert([
            'operation'=> OperationEnum::INCREASE,
            'operation_id' => $this->request->getOperationId(),
            'sender' => null,
            'recipient' => $target->getIdentity(),
            'amount' => $this->request->getAmount(),
            'description'=> $this->request->getDescription(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
