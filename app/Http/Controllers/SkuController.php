<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSkuRequest;
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
        $skus = Sku::paginate(perPage: 10, page: $page)
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

    public function create(CreateSkuRequest $request)
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
}
