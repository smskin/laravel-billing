<?php

namespace SMSkin\Billing\Traits;

trait RoundTrait
{
    protected function round(float $amount): float
    {
        $mod = config('billing.rounding.mod');
        $precision = config('billing.rounding.precision');
        if (!$mod) {
            return $amount;
        }
        return round($amount, $precision, $mod);
    }
}
