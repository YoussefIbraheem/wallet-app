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

    public function execute(string $body)
    {
        $data = $this->convertToArray($body);
        $webhook_id = Uuid::uuid4();
        foreach ($data as $trans) {
            $rawLineHashed = hash("sha256", $trans);
            if (
                Transaction::where("raw_line_hashed", $rawLineHashed)->exists()
            ) {
                continue;
            }
            Transaction::query()->create([
                "webhook_id" => $webhook_id,
                "raw_line" => $trans,
                "raw_line_hashed" => $rawLineHashed,
            ]);
        }

        // TODO add the parsing event here

        return $data;
    }

    /**
     * convert data to array
     *
     * @param string $body
     * @return array
     *
     */
    private function convertToArray(string $body): array
    {
        $cleanBody =
            preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $body) ?:
            $body;
        $dataArr = explode("\n", $cleanBody);

        return $dataArr;
    }
}
