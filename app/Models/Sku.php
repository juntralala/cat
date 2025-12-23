<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    ];

    protected $with = ['item'];

    public function item()
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