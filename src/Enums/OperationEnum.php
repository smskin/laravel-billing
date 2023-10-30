<?php

namespace SMSkin\Billing\Enums;

enum OperationEnum: string
{
    case INCREASE = 'INCREASE';
    case DECREASE = 'DECREASE';
    case TRANSFER = 'TRANSFER';
}
