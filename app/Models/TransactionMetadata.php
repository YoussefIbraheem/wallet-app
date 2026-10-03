<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable("parsed_transaction_id", "key", "value")]
class TransactionMetadata extends Model
{
    public function parsedTransaction(): BelongsTo
    {
        return $this->belongsTo(ParsedTransaction::class);
    }

    public $timestamps = false;
}
