<?php

namespace SMSkin\Billing\Models;

class ReportMeta
{
    protected int $lastPage;
    protected int $currentPage;
    protected int $itemsCount;
    protected int $perPage;

    /**
     * @param int $lastPage
     * @return ReportMeta
     */
    public function setLastPage(int $lastPage): self
    {
        $this->lastPage = $lastPage;
        return $this;
    }

    /**
     * @return int
     */
    public function getLastPage(): int
    {
        return $this->lastPage;
    }

    /**
     * @param int $currentPage
     * @return ReportMeta
     */
    public function setCurrentPage(int $currentPage): self
    {
        $this->currentPage = $currentPage;
        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * @param int $itemsCount
     * @return ReportMeta
     */
    public function setItemsCount(int $itemsCount): self
    {
        $this->itemsCount = $itemsCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getItemsCount(): int
    {
        return $this->itemsCount;
    }

    /**
     * @param int $perPage
     * @return ReportMeta
     */
    public function setPerPage(int $perPage): self
    {
        $this->perPage = $perPage;
        return $this;
    }

    /**
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }
}
