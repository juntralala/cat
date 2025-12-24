<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUpdateSkuRequest;
use App\Models\Item;
use App\Models\Sku;
use App\Models\MeasurementUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SkuController extends Controller
{
    public function page(Request $request): \Inertia\Response
    {
        $page = $request->integer('page', 1);
        $skus = Sku::with('skuMeasurementUnitConversions')
            ->paginate(perPage: 10, page: $page)
            ->withQueryString();
        $items = Item::all(['id', 'name']);
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
                    'spesification_name',
                    'quantity',
                    'price',
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
}
