<?php

namespace SMSkin\Billing\Actions;

use Illuminate\Support\Collection;
use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Database\BillingOperation;
use SMSkin\Billing\Enums\OperationEnum;
use SMSkin\Billing\Models\Payment;

class CreateTransferOperation
{
    /**
     * @param Billingable $sender
     * @param Collection<Payment> $payments
     */
    public function __construct(
        private readonly Billingable $sender,
        private readonly Collection $payments
    ) {
    }

    public function execute(): void
    {
        BillingOperation::query()->insert($this->payments->map(function (Payment $payment) {
            $recipient = $payment->getRecipient();

            return [
                'operation' => OperationEnum::TRANSFER,
                'operation_id' => $payment->getOperationId(),
                'sender' => $this->sender->getIdentity(),
                'recipient' => $recipient->getIdentity(),
                'amount' => $payment->getAmount(),
                'description' => $payment->getDescription(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray());
    }
}
