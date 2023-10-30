<?php

namespace SMSkin\Billing\Exceptions;

class InsufficientBalance extends BillingException
{
    public function __construct(protected float $balance, protected float $amount)
    {
        parent::__construct();
    }

    /**
     * @return float
     */
    public function getBalance(): float
    {
        return $this->balance;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }
}
