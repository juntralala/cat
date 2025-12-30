<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUpdateSkuRequest;
use App\Models\Item;
use App\Models\Sku;
use App\Models\MeasurementUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class SkuController extends Controller
{
    public function page(Request $request): \Inertia\Response
    {
        $page = $request->integer('page', 1);
        $skus = Sku::with('item:id,name,base_measurement_unit_id')
            ->with('item.baseMeasurementUnit:id,name')
            ->with('skuMeasurementUnitConversions')
            ->paginate(perPage: 10, page: $page)
            ->withQueryString();
        $items = Item::with('baseMeasurementUnit:id, name')->get(['id', 'name']);
        $derivedMeasurementUnitPerSku = MeasurementUnit::where('is_base', false)
            ->whereNull('base_measurement_unit_id')
            ->whereNull('conversion')
            ->get(['id', 'name']);
        return Inertia::render('Sku', [
            'skus' => $skus,
            'derivedMeasurementUnitPerSku' => $derivedMeasurementUnitPerSku,
            'items' => $items
        ]);
    }

    public function create(CreateUpdateSkuRequest $request)
    {
        $safe = $request->safe();
        DB::transaction(function () use ($safe) {
            $sku = Sku::create(
                $safe->only(
                    'item_id',
                    'sku',
                    'spesification_name',
                    'quantity',
                    'price',
                )
            );
            $sku->skuMeasurementUnitConversions()->createMany(
                $safe->array('sku_measurement_unit_conversions')
            );
        });
    }

    public function update(CreateUpdateSkuRequest $request, $skuId)
    {
        $safe = $request->safe();
        DB::transaction(function () use ($safe, $skuId) {
            $sku = Sku::findOrFail($skuId);
            $sku->update(
                $safe->only(
                    'item_id',
                    'sku',
                    'spesification_name',
                    'quantity',
                    'price'
                )
            );
            $sku->skuMeasurementUnitConversions()->delete();
            $sku->skuMeasurementUnitConversions()->createMany(
                $safe->array('sku_measurement_unit_conversions')
            );
        });
    }

    public function delete($skuId)
    {
        DB::transaction(function () use ($skuId) {
            $sku = Sku::findOrFail($skuId);
            $sku->skuMeasurementUnitConversions()->delete();
            $sku->delete();
        });
    }

    public function toXlsx(Request $request) {
        $callback = function() {
            try {
                $writer = new Writer();
                $writer->openToFile('php://output');
                $writer->addRow(Row::fromValues([
                    "No", "Nama", "SKU", "Nama Spesifikasi", "Stok", "Satuan Terkecil", "Harga Satuan Terkecil",
                ]));
    
                $no = 0;
                Sku::with('item.baseMeasurementUnit:id,name')->chunk(100, function($skus)use ($writer, &$no) {
                    foreach($skus as $sku) {
                        $writer->addRow(Row::fromValues([
                            ++$no,
                            $sku->item->name,
                            $sku->sku,
                            $sku->spesification_name,
                            $sku->quantity,
                            $sku->item->baseMeasurementUnit->name,
                            $sku->price
                        ]));
                    }
                });
            } finally {
                $writer->close();
            }
        };

        return response()->streamDownload($callback, "sku_" . (Date::now("+8")->format("d-m-Y")) . ".xlsx");
    }
}
