<?php

namespace SMSkin\Billing\Requests;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Requests\Models\Payment;
use Illuminate\Support\Collection;

class CreateTransferOperationRequest
{
    protected Billingable $sender;
    /**
     * @var Collection<Payment>
     */
    protected Collection $payments;

    /**
     * @param Billingable $sender
     * @return CreateTransferOperationRequest
     */
    public function setSender(Billingable $sender): self
    {
        $this->sender = $sender;
        return $this;
    }

    /**
     * @return Billingable
     */
    public function getSender(): Billingable
    {
        return $this->sender;
    }

    /**
     * @param Collection<Payment> $payments
     * @return CreateTransferOperationRequest
     */
    public function setPayments(Collection $payments): self
    {
        $this->payments = $payments;
        return $this;
    }

    /**
     * @return Collection<Payment>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }
}
