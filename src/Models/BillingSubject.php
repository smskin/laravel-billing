<?php

namespace SMSkin\Billing\Models;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Traits\BillingableTrait;

class BillingSubject implements Billingable
{
    use BillingableTrait;

    protected string $subsystem;

    protected string $type;

    protected string $id;

    public function getBillingSubsystem(): string
    {
        return $this->subsystem;
    }

    public function getBillingType(): string
    {
        return $this->type;
    }

    public function getBillingId(): string
    {
        return $this->id;
    }

    /**
     * @param string $subsystem
     * @return BillingSubject
     */
    public function setSubsystem(string $subsystem): self
    {
        $this->subsystem = $subsystem;
        return $this;
    }

    /**
     * @param string $type
     * @return BillingSubject
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param string $id
     * @return BillingSubject
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }
}
