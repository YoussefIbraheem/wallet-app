<?php

namespace App\Http\Controllers;

use App\Enums\Bank;
use App\Events\BankStatementReceived;
use App\Services\BankResponseHandler;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Process bank transaction response.
     *
     * @param Request $request
     * @return mixed
     */
    public function handleBankWebhook(Request $request, string $bankName)
    {
        try {
            $body = $request->getContent();

            if (empty($body)) {
                throw new \Exception("Empty data received");
            }

            BankStatementReceived::dispatch($body, $bankName);

            return response()->noContent();
        } catch (\Exception $e) {
            return response()->json(["error" => "Invalid request " . $e], 400);
        }
    }
}
