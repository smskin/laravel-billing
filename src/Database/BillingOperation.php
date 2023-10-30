<?php

namespace SMSkin\Billing\Database;

use SMSkin\Billing\Database\Casts\BillingableCast;
use Illuminate\Database\Eloquent\Model;
use SMSkin\Billing\Enums\OperationEnum;

class BillingOperation extends Model
{
    protected $casts = [
        'operation' => OperationEnum::class,
        'sender' => BillingableCast::class,
        'recipient' => BillingableCast::class
    ];

    public function getTable(): string
    {
        return config('billing.table');
    }
}
