<?php

namespace SMSkin\Billing\Controllers;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Models\Balance;
use SMSkin\Billing\Requests\GetBalancesRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetBalances
{
    public function __construct(protected GetBalancesRequest $request)
    {
    }

    /**
     * @return Collection<Balance>
     */
    public function execute(): Collection
    {
        if ($this->request->getAccounts()->isEmpty()) {
            return collect();
        }

        $balances = $this->getBalances();
        return $this->request->getAccounts()->map(static function (Billingable $account) use ($balances) {
            $hash = $account->getIdentityHash();
            return (new Balance())
                ->setAccount($account)
                ->setBalance($balances->$hash);
        });
    }

    private function getBalances(): object
    {
        $query = DB::query();
        $this->request->getAccounts()->each(static function (Billingable $account) use ($query) {
            $query->selectSub($account->getBalanceQuery(), $account->getIdentityHash());
        });
        return $query->first();
    }
}
