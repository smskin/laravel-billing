<?php

namespace SMSkin\Billing\Contracts;

use Illuminate\Database\Query\Builder;

interface Billingable
{
    public function getBillingSubsystem(): string;

    public function getBillingType(): string;

    public function getBillingId(): string;

    public function getIdentity(): string;

    public function getIdentityHash(): string;

    public function getBalanceQuery(): Builder;

    public function getBalance(): float;

    public function isEqual(self $billingable): bool;
}
