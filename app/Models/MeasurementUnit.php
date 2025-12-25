<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property MeasurementUnit $baseMeasurementUnit
 * @property MeasurementUnit $derivedMeasurementUnits
 */
class MeasurementUnit extends Model
{
    use HasUuids, SoftDeletes;

    protected  $table = 'measurement_units';
    protected  $primaryKey = 'id';
    protected  $keyType = 'string';
    public $incrementing = false;
    public $timstamps = true;

    protected $casts = [
        'is_base' => 'boolean'
    ];

    protected  $fillable = [
        'name',
        'base_measurement_unit_id',
        'conversion',
        'is_base'
    ];

    // self reference
    public function baseMeasurementUnit(): BelongsTo {
        return $this->belongsTo(MeasurementUnit::class, 'base_measurement_unit_id', 'id');
    }

    public function derivedMeasurementUnits(): HasMany { // satuan turunan
        return $this->hasMany(MeasurementUnit::class, 'base_measurement_unit_id', 'id');
    }

    public function basedMeasurementUnitItems(): HasMany
    {
        return $this->hasMany(Item::class, 'base_measurement_unit_id', 'id');
    }

    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class, 'measurement_unit_id', 'id');
    }

    public function skuMeasurementUnitConversions(): HasMany {
        return $this->hasMany(SkuMeasurementUnitConversion::class, 'measurement_unit_id', 'id');
    }
}
