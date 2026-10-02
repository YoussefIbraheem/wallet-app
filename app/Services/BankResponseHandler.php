<?php

namespace App\Services;

use App\BankParser\BankParserRegistry;
use App\Models\Transaction;
use Ramsey\Uuid\Uuid;

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
        $webhook_id = Uuid::uuid4();
        foreach ($data as $ref => $trans) {
            $rawLineHashed = hash("sha256", $trans);
            if (Transaction::where("raw_line_hashed", $rawLineHashed)->exists()) {
                continue;
            }
            Transaction::query()->create([
                "webhook_id" => $webhook_id,
                "reference" => $ref,
                "raw_line" => $trans,
                "raw_line_hashed" => $rawLineHashed,
            ]);
        }

        return $data;
    }
}
