<?php
namespace App\Repository;

use App\Dto\Response\ExpenditurePerSKU;
use App\Dto\Response\PaginatedResponse;
use App\Models\Sku;
use Illuminate\Support\Facades\DB;

class DashboardRepository
{

    // tempat membuat query builder, terminate method di tempat lain
    private function expenditureQuery($search, $start, $end) {
        return Sku::with([
            'item.baseMeasurementUnit:id,name',
            'transactionItems' => function ($query) use ($start, $end) {
                $query->whereHas('transaction', function ($tq) use ($start, $end) {
                    $tq->where('type', 'out');
                    $tq->whereBetween('transaction_date', [$start, $end]);
                });
            }
        ])
            ->when($search , function($query) use ($search) {
                $query->orWhereLike('sku', "%$search%");
                $query->orWhereLike('spesification_name', "%$search%");
                $query->orWhereHas('item', fn($iq) => $iq->whereLike('name', "%$search%"));
            })
            ->withSum(['transactionItems as total_quantity' => function($q) {
                $q->whereHas('transaction', fn($tq) => $tq->where('type', 'out'));
            }], 'base_quantity')
            ->withSum(['transactionItems as expenditure' => function($q) {
                $q->whereHas('transaction', fn($tq) => $tq->where('type', 'out'));
            }], DB::raw("base_quantity * price"))
            ->withAvg([
                'transactionItems as out_price' => function ($q) {
                    $q->whereHas('transaction', fn($tq) => $tq->where('type', 'out'));
                }
            ], 'price');
    }

    public function getExpendituresPerSKU($search = null, $start = null, $end = null, $page = 1)
    {
        $expenditures = collect();
        $skus = $this->expenditureQuery($search, $start, $end)
            ->paginate(perPage: 10, page: $page);

        foreach ($skus as $sku) {
            $expenditures->add(new ExpenditurePerSKU(
                $sku->item->name,
                $sku->sku,
                $sku->spesification_name,
                $sku->total_quantity ?? 0,
                $sku->item->baseMeasurementUnit->name,
                $sku->out_price ?? $sku->price ?? 0,
                $sku->expenditure ?? 0
            ));
        }

        return new PaginatedResponse(
            $expenditures,
            $skus->currentPage(),
            $skus->perPage(),
            $skus->lastPage(),
            $skus->total(),
        );
    }

    public function exportXlsx($callback, $start, $end) {
        return $this->expenditureQuery(null, $start, $end)
            ->chunk(100, $callback);
    }
}