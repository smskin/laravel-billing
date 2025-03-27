<?php

namespace SMSkin\Billing\Events\Enums;

enum BulkTransferFailedReasonEnum: int
{
    case AMOUNT_MUST_BE_MORE_THAN_0 = 1;
    case NOT_UNIQUE_OPERATION_ID = 2;
    case INSUFFICIENT_BALANCE = 3;
    case RECIPIENT_IS_SENDER = 4;
}
