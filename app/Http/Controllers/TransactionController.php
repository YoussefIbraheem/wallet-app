<?php

namespace App\Http\Controllers;

use App\Enums\Bank;
use App\Events\WebhookReceived;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Bank Webhook
     *
     * A dynamic webhook that relies on the bankName to choose the parser that would interperate the bank response into transactions.
     *
     * @bodyParam body string required A text content of the bank transactions the entered data must adhere to format stated in the parser <aside>Check BankParser for more details</aside>. Example: 20250615156,50#202506159000001#note/debt payment march/internal_reference/A462JE81
     *
     * @urlParam bankName string required A registered bank name that is configured for parsing and registered in BankParserService Provider <aside>All in lowercase</aside>. Example: hsbc
     *
     * @response 204 {}
     */
    public function handleBankWebhook(Request $request, string $bankName)
    {
        try {
            $body = $request->getContent();

            if (empty($body)) {
                throw new \Exception('Empty data received');
            }

            WebhookReceived::dispatch($body, $bankName);

            return response()->noContent();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid request '.$e], 400);
        }
    }
}
