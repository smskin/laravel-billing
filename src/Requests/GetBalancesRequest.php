<?php

namespace SMSkin\Billing\Requests;

use SMSkin\Billing\Contracts\Billingable;
use Illuminate\Support\Collection;

class GetBalancesRequest
{
    /**
     * @var Collection<Billingable>
     */
    protected Collection $accounts;

    /**
     * @param Collection<Billingable> $accounts
     * @return GetBalancesRequest
     */
    public function setAccounts(Collection $accounts): self
    {
        $this->accounts = $accounts;
        return $this;
    }

    /**
     * @return Collection<Billingable>
     */
    public function getAccounts(): Collection
    {
        return $this->accounts;
    }
}
