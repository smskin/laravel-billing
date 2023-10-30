<?php

namespace SMSkin\Billing\Actions;

use SMSkin\Billing\Database\BillingOperation;
use SMSkin\Billing\Enums\OperationEnum;
use SMSkin\Billing\Requests\CreateTransferOperationRequest;
use SMSkin\Billing\Requests\Models\Payment;

class CreateTransferOperation
{
    public function __construct(protected CreateTransferOperationRequest $request)
    {
    }

    public function execute(): void
    {
        $sender = $this->request->getSender();

        BillingOperation::query()->insert($this->request->getPayments()->map(static function (Payment $payment) use ($sender) {
            $recipient = $payment->getRecipient();

            return [
                'operation' => OperationEnum::TRANSFER,
                'operation_id' => $payment->getOperationId(),
                'sender' => $sender->getIdentity(),
                'recipient' => $recipient->getIdentity(),
                'amount' => $payment->getAmount(),
                'description' => $payment->getDescription(),
                'created_at' => now(),
                'updated_at' => now()
            ];
        })->toArray());
    }
}
