<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkuMeasurementUnitConversion extends Model
{
    use HasUuids;

    protected $table = 'sku_measurement_unit_conversions';

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = true;

    protected $fillable = [
        "measurement_unit_id",
        "item_sku_id",
        "conversion"
    ];

    public function unit(): BelongsTo  {
        return $this->belongsTo(MeasurementUnit::class, 'measurement_unit_id', 'id');
    }

    public function sku(): BelongsTo  {
        return $this->belongsTo(Sku::class, 'sku_id', 'id');
    }
}
