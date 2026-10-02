<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable("transaction_id", "reference", "amount", "date")]
class ParsedTransaction extends Model
{
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, "transaction_id", "id");
    }
}
