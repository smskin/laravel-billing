<?php

namespace SMSkin\Billing\Events;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Events\Enums\BalanceDecreaseFailedReasonEnum;

class EDecreaseBalanceFailed extends EDecreaseBalance
{
    public function __construct(public string $operationId, public Billingable $target, public float $amount, public BalanceDecreaseFailedReasonEnum $reason)
    {
        parent::__construct($this->operationId, $this->target, $this->amount);
    }
}
