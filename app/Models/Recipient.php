<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipient extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'recipients';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = true;

    protected $fillable = [
        'name',
        'nickname',
        'division',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'recipient_id', 'id');
    }
}
