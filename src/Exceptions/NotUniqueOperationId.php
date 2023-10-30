<?php

namespace SMSkin\Billing\Exceptions;

use Illuminate\Database\UniqueConstraintViolationException;

class NotUniqueOperationId extends BillingException
{
    public function __construct(protected string $operationId, UniqueConstraintViolationException $exception)
    {
        parent::__construct(null, 0, $exception);
    }

    /**
     * @return string
     */
    public function getOperationId(): string
    {
        return $this->operationId;
    }
}
