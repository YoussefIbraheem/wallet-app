<?php

namespace App\BankParser;

class PayTech implements BankParser
{
    public function name(): string
    {
        return "paytech";
    }

    public function parse(string $body): array
    {
        //
    }

    /**
     * Breaksdown the transactions into unique transactions and store them in the database.
     *
     * @param string $body
     * @return array
     *
     */
    public function process(string $body): array
    {
        $dataArrUnique = $this->convertToUniqueArray($body);

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
        $delimiter = "#";

        $refStart = strpos($transaction, $delimiter);
        if ($refStart === false) {
            return "";
        }

        $refStart += strlen($delimiter);

        $refEnd = strpos($transaction, $delimiter, $refStart);
        if ($refEnd === false) {
            return "";
        }

        return substr($transaction, $refStart, $refEnd - $refStart);
    }
}
