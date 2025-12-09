<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'stocks';

    protected $keyType = 'string';
    protected $primaryKey = 'id';

    public $incrementing = false;
    
    public $timestamps = false;
    

    protected $fillable = [
        'item_id',
        'unit_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(MeasurementUnit::class, 'unit_id', 'id');
    }
}