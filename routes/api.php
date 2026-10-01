<?php

use App\Enums\Bank;
use App\Services\BankResponseHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post("webhook/paytech/payment", function (Request $request) {
    $response = new BankResponseHandler(Bank::PAYTECH)->execute(
        $request->getContent(),
    );
    dump($response);
});

Route::post("webhook/acme/payment", function (Request $request) {
    $response = new BankResponseHandler(Bank::ACME)->execute(
        $request->getContent(),
    );
    dump($response);
});
