<?php

namespace SMSkin\Billing\Models;

use Illuminate\Support\Collection;

class Report
{
    protected ReportMeta $meta;

    /**
     * @var Collection<ReportOperation>
     */
    protected Collection $operations;

    /**
     * @param ReportMeta $meta
     * @return Report
     */
    public function setMeta(ReportMeta $meta): self
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     * @return ReportMeta
     */
    public function getMeta(): ReportMeta
    {
        return $this->meta;
    }

    /**
     * @param Collection<ReportOperation> $operations
     * @return Report
     */
    public function setOperations(Collection $operations): self
    {
        $this->operations = $operations;
        return $this;
    }

    /**
     * @return Collection<ReportOperation>
     */
    public function getOperations(): Collection
    {
        return $this->operations;
    }
}
