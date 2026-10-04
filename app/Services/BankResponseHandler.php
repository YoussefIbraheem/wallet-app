<?php

namespace App\Services;

use App\Events\TransactionParse;
use App\Models\Transaction;
use Ramsey\Uuid\Uuid;

class BankResponseHandler
{
    public function __construct()
    {
        //
    }

    public function execute(string $body, string $bankName)
    {
        $data = $this->convertToArray($body);
        $webhookId = Uuid::uuid4();
        $transactions = [];
        foreach ($data as $trans) {
            $rawLineHashed = hash("sha256", $trans);
            if (Transaction::where("raw_line_hashed", $rawLineHashed)->exists() ) {
                continue;
            }
            $transactions[$rawLineHashed] = [
                "webhook_id" => $webhookId,
                "bank_name" => $bankName,
                "raw_line" => $trans,
                "raw_line_hashed" => $rawLineHashed,
            ];
        }

        $transactions = $this->removeDuplicates($transactions);

        Transaction::query()->insert($transactions);

        TransactionParse::dispatch($webhookId, $bankName);

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

    private function removeDuplicates(array $transactions): array
    {
        $uniqueArr = [];

        foreach ($transactions as $trans) {
            $ref = $trans["raw_line_hashed"];
            if (!array_key_exists($ref, $uniqueArr)) {
                $uniqueArr[$ref] = $trans;
            }
        }

        return array_values($uniqueArr);
    }


}
