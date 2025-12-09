<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionDetail extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'transaction_details';

    protected $keyType = 'string';
    
    protected $primaryKey = 'id';

    public $incrementing = false;
    
    public $timestamps = true;

    protected $fillable = [
        'transaction_id',
        'item_id',
        'unit_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    protected $with = [
        'item',
        'unit',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(MeasurementUnit::class, 'unit_id', 'id');
    }
}
