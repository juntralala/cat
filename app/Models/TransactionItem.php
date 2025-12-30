<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property \App\Models\MeasurementUnit $unit
 */
class TransactionItem extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'transaction_items';

    protected $keyType = 'string';
    
    protected $primaryKey = 'id';

    public $incrementing = false;
    
    public $timestamps = true;

    protected $fillable = [
        'transaction_id',
        'sku_id',
        'measurement_unit_id',
        'price',
        'quantity',
        'base_quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'double',
    ];

    protected $with = [
        'sku',
        'unit',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(Sku::class, 'sku_id', 'id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(MeasurementUnit::class, 'measurement_unit_id', 'id');
    }
}
