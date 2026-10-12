<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Override;

#[Fillable('transaction_id', 'reference', 'amount', 'date')]
class ParsedTransaction extends Model
{

    #[Override]
    protected function casts()
    {
        return [
            "amount" => "decimal"
        ];
    }
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function transactionMetadata(): HasManyThrough
    {
        return $this->hasManyThrough(TransactionMetadata::class, Transaction::class);
    }

    protected function amount(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value / 100,
            set: fn($value) => $value * 100
        );
    }
}
