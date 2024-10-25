<?php

namespace SMSkin\Billing\Actions;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Database\BillingOperation;
use SMSkin\Billing\Enums\OperationEnum;

class CreateIncreaseBalanceOperation
{
    public function __construct(
        private readonly string $operationId,
        private readonly Billingable $target,
        private readonly float $amount,
        private readonly string|null $description = null
    )
    {
    }

    public function execute(): void
    {
        BillingOperation::query()->insert([
            'operation' => OperationEnum::INCREASE,
            'operation_id' => $this->operationId,
            'sender' => null,
            'recipient' => $this->target->getIdentity(),
            'amount' => $this->amount,
            'description' => $this->description,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
