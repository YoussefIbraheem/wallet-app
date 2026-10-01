<?php

namespace App\Services;

use App\BankParser\BankParserRegistry;
use App\Models\Transaction;

class BankResponseHandler
{
    public function __construct(private BankParserRegistry $parsers)
    {
        //
    }

    public function execute(string $body, string $bankName)
    {
        $parser = $this->parsers->get($bankName);

        $data = $parser->process($body);

        foreach ($data as $trans) {
            Transaction::query()->create(["transaction" => $trans]);
        }

        return $data;
    }
}
