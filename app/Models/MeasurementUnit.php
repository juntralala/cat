<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeasurementUnit extends Model
{
    use HasUuids, SoftDeletes;

    protected  $table = 'measurement_units';
    protected  $primaryKey = 'id';
    protected  $keyType = 'string';
    public $incrementing = false;
    public $timstamps = true;

    protected  $fillable = [
        'name'
    ];

    public function stocks()
    {
        return $this->hasMany(Stock::class, 'unit_id', 'id');
    }

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class, 'unit_id', 'id');
    }
}
