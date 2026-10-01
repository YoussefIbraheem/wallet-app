<?php

namespace App\Services;

use App\Enums\Bank;
use App\Models\Transaction;

class BankResponseHandler
{
    private Bank $bankName;

    public function __construct(Bank $bankName)
    {
        $this->bankName = $bankName;
    }

    /**
     * Execute main class function
     *
     * @param string $body
     * @return array
     *
     */
    public function execute(string $body): array
    {
        $dataArrUnique = $this->convertToUniqueArray($body);

        Transaction::query()->insert($dataArrUnique);

        return $dataArrUnique;
    }

    /**
     * convert data to unique array
     *
     * @param string $body
     * @return array
     *
     */
    private function convertToUniqueArray(string $body): array
    {
        $cleanBody =
            preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $body) ?:
            $body;
        $dataArr = explode("\n", $cleanBody);
        $dataArrUnique = $this->removeDuplicates($dataArr);

        return $dataArrUnique;
    }

    /**
     * Remove Duplicate transactions
     *
     * @param array $dataArr
     * @return array
     *
     */
    private function removeDuplicates(array $dataArr): array
    {
        $refArr = [];

        foreach ($dataArr as $trans) {
            $ref = $this->extractReference($trans);
            if (!array_key_exists($ref, $refArr)) {
                $refArr[$ref] = $trans;
            }
        }

        return array_values($refArr);
    }

    /**
     * Extract reference depnding on given bank name
     *
     * @param string $transaction
     * @return string
     *
     */
    private function extractReference(string $transaction): string
    {
        $symbol = $this->determineBankRefSymbol();

        $refStart = strpos($transaction, $symbol);
        if ($refStart === false) {
            return "";
        }

        $refStart += strlen($symbol);

        $refEnd = strpos($transaction, $symbol, $refStart);
        if ($refEnd === false) {
            return "";
        }

        return substr($transaction, $refStart, $refEnd - $refStart);
    }

    /**
     * determine reference coating symbol based on bank name
     * @return string
     */
    private function determineBankRefSymbol(): string
    {
        $symbol = $this->bankName->attributes()["ref_symbol"];

        if (!$symbol) {
            throw new \InvalidArgumentException("Invalid bank name");
        }

        return $symbol;
    }
}
