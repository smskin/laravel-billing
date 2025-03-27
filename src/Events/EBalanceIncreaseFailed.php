<?php

namespace SMSkin\Billing\Events;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Events\Enums\BalanceIncreaseFailedReasonEnum;

class EBalanceIncreaseFailed extends EBalanceIncrease
{
    public function __construct(public string $operationId, public Billingable $target, public float $amount, public string|null $description, public BalanceIncreaseFailedReasonEnum $reason)
    {
        parent::__construct($this->operationId, $this->target, $this->amount, $this->description);
    }
}
