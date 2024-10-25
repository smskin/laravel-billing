<?php

namespace SMSkin\Billing\Database\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Models\BillingSubject;

class BillingableCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): BillingSubject|null
    {
        if ($value === null) {
            return null;
        }

        list($subsystem, $type, $id) = explode('-', $value);

        return (new BillingSubject())
            ->setSubsystem($subsystem)
            ->setType($type)
            ->setId($id);
    }

    public function set($model, string $key, $value, array $attributes): string|null
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof Billingable) {
            throw new InvalidArgumentException('The given value is not an Billingable instance.');
        }

        return $value->getIdentity();
    }
}
