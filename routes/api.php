<?php

use App\Enums\Bank;
use App\Http\Controllers\TransactionController;
use App\Services\WebhookHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post("webhook/{bank_name}/payment", [
    TransactionController::class,
    "handleBankWebhook",
]);
