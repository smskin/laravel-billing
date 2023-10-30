<?php

namespace SMSkin\Billing\Requests;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Requests\Models\Payment;
use Illuminate\Support\Collection;

class TransferToMultipleRecipientsRequest
{
    protected Billingable $sender;

    /**
     * @var Collection<Payment>
     */
    protected Collection $payments;

    /**
     * @param Billingable $sender
     * @return TransferToMultipleRecipientsRequest
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
     * @param Collection $payments
     * @return TransferToMultipleRecipientsRequest
     */
    public function setPayments(Collection $payments): self
    {
        $this->payments = $payments;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }
}
