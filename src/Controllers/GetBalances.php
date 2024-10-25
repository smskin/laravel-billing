<?php

namespace SMSkin\Billing\Controllers;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Models\Balance;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetBalances
{
    /**
     * @param Collection<Billingable> $accounts
     */
    public function __construct(private readonly Collection $accounts)
    {
    }

    /**
     * @return Collection<Balance>
     */
    public function execute(): Collection
    {
        if ($this->accounts->isEmpty()) {
            return collect();
        }

        $balances = $this->getBalances();
        return $this->accounts->map(static function (Billingable $account) use ($balances) {
            $hash = $account->getIdentityHash();
            return (new Balance())
                ->setAccount($account)
                ->setBalance($balances->$hash);
        });
    }

    private function getBalances(): object
    {
        $query = DB::query();
        $this->accounts->each(static function (Billingable $account) use ($query) {
            $query->selectSub($account->getBalanceQuery(), $account->getIdentityHash());
        });
        return $query->first();
    }
}
