<?php

namespace SMSkin\Billing\Events;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Events\Enums\FailedReasonEnum;

class EDecreaseBalanceFailed extends EDecreaseBalance
{
    public function __construct(public string $operationId, public Billingable $target, public float $amount, public FailedReasonEnum $reason)
    {
        parent::__construct($this->operationId, $this->target, $this->amount);
    }
}
