<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'transactions';

    protected $keyType = 'string';
    
    protected $primaryKey = 'id';

    public $incrementing = false;
    
    public $timestamps = true;

    protected $fillable = [
        'type',
        'recipient_id',
        'user_id',
        'supplier',
        'division',
        'notes',
        'transaction_date',
    ];

    protected $casts = [
        'transaction_date' => 'datetime:Y-m-d',
    ];

    protected $with = [
        'transactionItems',
    ];

    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class, 'transaction_id', 'id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Recipient::class, 'recipient_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
