<?php

namespace SMSkin\Billing\Models;

use SMSkin\Billing\Contracts\Billingable;

class Balance
{
    protected Billingable $account;
    protected float $balance;

    /**
     * @param Billingable $account
     * @return Balance
     */
    public function setAccount(Billingable $account): self
    {
        $this->account = $account;
        return $this;
    }

    /**
     * @return Billingable
     */
    public function getAccount(): Billingable
    {
        return $this->account;
    }

    /**
     * @param float $balance
     * @return Balance
     */
    public function setBalance(float $balance): self
    {
        $this->balance = $balance;
        return $this;
    }

    /**
     * @return float
     */
    public function getBalance(): float
    {
        return $this->balance;
    }
}
