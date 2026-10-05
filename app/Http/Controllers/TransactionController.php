<?php

namespace App\Http\Controllers;

use App\Enums\Bank;
use App\Events\WebhookReceived;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Process bank transaction response.
     *
     * @return mixed
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
