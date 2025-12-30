<?php

namespace App\Service;

use App\Models\MeasurementUnit;

class MeasurementUnitService
{
    public function __construct()
    {
    }

    // nerima id atau MeasurementUnit langsung
    // jangan sampai return 0 atau null, ini method sudah dipake dengan expektasi kayak gitu
    public function getConversion(string|MeasurementUnit $unit, string $skuId): int {
        if(!$unit instanceof MeasurementUnit) {
            $unit = MeasurementUnit::find($unit);
        }
        if($unit->is_base) {
            return 1;
        } else if($unit->baseMeasurementUnit()->withTrashed()->exists()) {
            return $unit->conversion;
        } else if ($skuMeasurementUnitConversion = $unit->skuMeasurementUnitConversions()->where('sku_id', $skuId)->first(['conversion'])) {
            return $skuMeasurementUnitConversion->conversion ?? 1;
        } else {
            abort(400, "Ukuran satuan '$unit->name' bukan satuan dasar dan tidak memiliki konversi yang terdaftar");
        }
    }
}
