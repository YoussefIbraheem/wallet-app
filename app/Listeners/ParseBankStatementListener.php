<?php

namespace App\Listeners;

use App\BankParser\BankParserRegistry;
use App\Events\ParseBankStatement;
use App\Models\ParsedTransaction;
use App\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;

class ParseBankStatementListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(public BankParserRegistry $registery)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ParseBankStatement $event): void
    {
        $parser = $this->registery->get($event->bankName);
        $transactions = Transaction::query()->where("webhook_id", $event->webhook_id)->get();
        foreach ($transactions as $transaction) {
            $parsedTransaction = $parser->parse($transaction->raw_line);
            ParsedTransaction::query()->create([
                "date" => $parsedTransaction["date"],
                "amount" => $parsedTransaction["amount"],
                "reference" => $parsedTransaction["reference"],
                "transaction_id" => $transaction->id,
            ]);
        }
    }
}
