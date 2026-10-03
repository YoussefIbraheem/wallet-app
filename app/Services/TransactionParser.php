<?php

namespace App\Services;

use App\BankParser\BankParserRegistry;
use App\Enums\TransactionStatus;
use App\Models\ParsedTransaction;
use App\Models\Transaction;

class TransactionParser
{
    /**
     * Create a new class instance.
     */
    public function __construct(public BankParserRegistry $registery) {}

    public function execute(string $webhook_id, string $bankName)
    {
        $parser = $this->registery->get($bankName);
        $transactions = Transaction::query()->where("webhook_id", $webhook_id)->get();
        foreach ($transactions as $transaction) {
            // if (!$parser->isTransactionFormatMatching($transaction)) {
            //     $transaction->update(["status" => TransactionStatus::FAILED->value]);
            //     continue;
            // }
            $parsedTransaction = $parser->parse($transaction->raw_line);
            ParsedTransaction::query()->create([
                "date" => $parsedTransaction["date"],
                "amount" => $parsedTransaction["amount"],
                "reference" => $parsedTransaction["reference"],
                "transaction_id" => $transaction->id,
            ]);
            $transaction->update(["status" => TransactionStatus::PROCESSED->value]);
        }
    }
}
