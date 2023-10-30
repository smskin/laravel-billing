<?php

namespace SMSkin\Billing\Requests;

class CreateOperationsReportRequest
{
    protected int $perPage = 25;
    protected int $page = 1;

    /**
     * @param int $perPage
     * @return CreateOperationsReportRequest
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

    /**
     * @param int $page
     * @return CreateOperationsReportRequest
     */
    public function setPage(int $page): self
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }
}
