<?php

namespace SMSkin\Billing\Events\Enums;

enum BalanceIncreaseFailedReasonEnum: int
{
    case AMOUNT_MUST_BE_MORE_THAN_0 = 1;
    case NOT_UNIQUE_OPERATION_ID = 2;
}
