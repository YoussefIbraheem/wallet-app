<?php

namespace App\Services;

use App\BankParser\BankParserRegistry;
use App\BankParser\HasMatchingFormat;
use App\BankParser\HasMetadata;
use App\Enums\TransactionStatus;
use App\Models\ParsedTransaction;
use App\Models\Transaction;
use App\Models\TransactionMetadata;
use Illuminate\Support\Facades\DB;

class TransactionParser
{
    /**
     * Create a new class instance.
     */
    public function __construct(public BankParserRegistry $registery) {}

    public function execute(string $webhookId, string $bankName)
    {
        $parser = $this->registery->get($bankName);
        $transactions = Transaction::query()->where('webhook_id', $webhookId)->get();
        $parsedTransactions = [];
        $metadata = [];
        foreach ($transactions as $transaction) {
            if ($parser instanceof HasMatchingFormat && ! $parser->isMatchingFormat($transaction->raw_line)) {
                $transaction->status = TransactionStatus::FAILED->value;

                continue;
            }
            $parsedTransaction = $parser->parse($transaction->raw_line);
            $parsedTransactions[] = [
                'date' => $parsedTransaction['date'],
                'amount' => $parsedTransaction['amount'],
                'reference' => $parsedTransaction['reference'],
                'transaction_id' => $transaction->id,
            ];
            if ($parser instanceof HasMetadata) {
                $metadata[] = $parser->parseMetadata($transaction->id, $parsedTransaction);
            }
        }

        ParsedTransaction::query()->insert($parsedTransactions);
        if ($parser instanceof HasMetadata) {
            $metadata = array_merge(...$metadata);
            TransactionMetadata::query()->insert($metadata);
        }

        $transactions = $transactions->toArray();

        DB::table('transactions')->upsert($transactions, ['id'], ['status']);

    }
}
