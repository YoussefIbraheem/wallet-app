<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable("transaction_id", "reference", "amount", "date")]
class ParsedTransaction extends Model
{
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function transactionMetadata(): HasMany
    {
        return $this->hasMany(TransactionMetadata::class, "parsed_transaction_id", "id");
    }
}
