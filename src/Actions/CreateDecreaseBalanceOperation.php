<?php

namespace SMSkin\Billing\Actions;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Database\BillingOperation;
use SMSkin\Billing\Enums\OperationEnum;

class CreateDecreaseBalanceOperation
{
    public function __construct(
        private readonly string $operationId,
        private readonly Billingable $target,
        private readonly float $amount,
        private readonly string|null $description
    ) {
    }

    public function execute(): void
    {
        BillingOperation::query()->insert([
            'operation' => OperationEnum::DECREASE,
            'operation_id' => $this->operationId,
            'sender' => $this->target->getIdentity(),
            'recipient' => null,
            'amount' => $this->amount,
            'description' => $this->description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
