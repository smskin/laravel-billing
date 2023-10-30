<?php

namespace SMSkin\Billing\Actions;

use SMSkin\Billing\Database\BillingOperation;
use SMSkin\Billing\Enums\OperationEnum;
use SMSkin\Billing\Requests\BalanceOperationRequest;

class CreateDecreaseBalanceOperation
{
    public function __construct(protected BalanceOperationRequest $request)
    {
    }

    public function execute(): void
    {
        $target = $this->request->getTarget();

        BillingOperation::query()->insert([
            'operation' => OperationEnum::DECREASE,
            'operation_id' => $this->request->getOperationId(),
            'sender' => $target->getIdentity(),
            'recipient' => null,
            'amount' => $this->request->getAmount(),
            'description' => $this->request->getDescription(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
