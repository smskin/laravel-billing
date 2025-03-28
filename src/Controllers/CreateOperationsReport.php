<?php

namespace SMSkin\Billing\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use SMSkin\Billing\Database\BillingOperation;
use SMSkin\Billing\Models\Report;
use SMSkin\Billing\Models\ReportMeta;
use SMSkin\Billing\Models\ReportOperation;

class CreateOperationsReport
{
    public function __construct(
        private readonly int $page,
        private readonly int $perPage
    ) {
    }

    public function execute(): Report
    {
        $operations = $this->getOperations();

        return (new Report())
            ->setMeta($this->prepareMeta($operations))
            ->setOperations($this->prepareOperations($operations));
    }

    private function getOperations(): LengthAwarePaginator
    {
        /** @noinspection UnknownColumnInspection */
        return BillingOperation::query()->paginate(
            $this->perPage,
            [
                'id',
                'operation_id',
                'operation',
                'sender',
                'recipient',
                'amount',
                'description',
                'created_at',
            ],
            'page',
            $this->page
        );
    }

    private function prepareMeta(LengthAwarePaginator $operations): ReportMeta
    {
        return (new ReportMeta())
            ->setLastPage($operations->lastPage())
            ->setCurrentPage($operations->currentPage())
            ->setPerPage($operations->perPage())
            ->setItemsCount($operations->total());
    }

    /**
     * @param LengthAwarePaginator $operations
     * @return Collection<ReportOperation>
     */
    private function prepareOperations(LengthAwarePaginator $operations): Collection
    {
        return collect($operations->items())->map(static function (BillingOperation $operation) {
            return (new ReportOperation())
                ->setOperationId($operation->getAttribute('operation_id'))
                ->setOperation($operation->getAttribute('operation'))
                ->setSender($operation->getAttribute('sender'))
                ->setRecipient($operation->getAttribute('recipient'))
                ->setAmount($operation->getAttribute('amount'))
                ->setDescription($operation->getAttribute('description'))
                ->setCreatedAt($operation->getAttribute('created_at'));
        });
    }
}
