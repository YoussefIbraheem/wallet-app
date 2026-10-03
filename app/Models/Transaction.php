<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable("webhook_id", "bank_name", "raw_line", "raw_line_hashed", "status")]
class Transaction extends Model
{
    use HasFactory;
}
