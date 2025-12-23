<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'item_sku_id',
        'unit_id',
        'price',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    protected $with = [
        'itemSku',
        'unit',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }

    public function itemSku()
    {
        return $this->belongsTo(Sku::class, 'sku_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(MeasurementUnit::class, 'unit_id', 'id');
    }
}
