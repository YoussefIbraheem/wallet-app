<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable('transaction_id', 'reference', 'amount', 'date')]
class ParsedTransaction extends Model
{
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function transactionMetadata(): HasManyThrough
    {
        return $this->hasManyThrough(TransactionMetadata::class, Transaction::class);
    }
}
