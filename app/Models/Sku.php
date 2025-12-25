<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\SkuMeasurementUnitConversion> $skuMeasurementUnitConversions
 */
class Sku extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'skus';

    protected $keyType = 'string';
    protected $primaryKey = 'id';

    public $incrementing = false;

    public $timestamps = false;


    protected $fillable = [
        'item_id',
        'sku',
        'quantity',
        'spesification_name',
        'price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'double',
    ];

    protected $with = ['item'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class, 'sku_id', 'id');
    }

    public function skuMeasurementUnitConversions(): HasMany {
        return $this->hasMany(SkuMeasurementUnitConversion::class, 'sku_id', 'id');
    }
}