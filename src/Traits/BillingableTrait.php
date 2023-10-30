<?php

namespace SMSkin\Billing\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SMSkin\Billing\Contracts\Billingable;
use Illuminate\Database\Query\Builder;
use SMSkin\Billing\Database\BillingOperation;

trait BillingableTrait
{
    abstract function getBillingSubsystem(): string;

    abstract function getBillingType(): string;

    abstract function getBillingId(): string;

    public function getIdentity(): string
    {
        return Str::lower(trim($this->getBillingSubsystem() . '-' . $this->getBillingType() . '-' . $this->getBillingId()));
    }

    public function getIdentityHash(): string
    {
        return md5($this->getIdentity());
    }

    public function isEqual(Billingable $billingable): bool
    {
        return $this->getIdentity() === $billingable->getIdentity();
    }

    public function getBalanceQuery(): Builder
    {
        $identity = $this->getIdentity();
        /** @noinspection UnknownColumnInspection */
        return DB::query()
            ->selectRaw('(incoming-outgoing) as balance')
            ->fromSub(
                DB::query()
                    ->selectSub(
                        BillingOperation::query()->selectRaw('COALESCE(SUM(amount), 0)')
                            ->where('recipient', $identity),
                        'incoming'
                    )
                    ->selectSub(
                        BillingOperation::query()->selectRaw('COALESCE(SUM(amount), 0)')
                            ->where('sender', $identity),
                        'outgoing'
                    ),
                'balances'
            );
    }

    public function getBalance(): float
    {
        $result = $this->getBalanceQuery()->first();
        return round($result->balance, config('billing . rounding . precision'), PHP_ROUND_HALF_DOWN);
    }
}
