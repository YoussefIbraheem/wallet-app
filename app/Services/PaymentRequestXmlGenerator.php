<?php

namespace App\Services;

use App\DTOs\PaymentRequestDto;
use XMLWriter;

class PaymentRequestXmlGenerator
{
    /**
     * Create a new class instance.
     */
    public function __construct() {}

    public function execute(PaymentRequestDto $data): string
    {

        $xw = xmlwriter_open_memory();

        xmlwriter_start_document($xw, "1.0", "UTF-8");

        xmlwriter_start_element($xw, "PaymentRequestMessage");
        $this->TransferInfo($xw, $data);
        $this->senderInfo($xw, $data);
        $this->receiverInfo($xw, $data);
        $this->notes($xw, $data);
        $this->paymentType($xw, $data);
        $this->ChargeDetails($xw, $data);
        xmlwriter_end_element($xw);

        xmlwriter_end_document($xw);

        return xmlwriter_output_memory($xw);
    }

    private function TransferInfo(XMLWriter $xml, PaymentRequestDto $data)
    {
        $reference = $data->reference;
        $date = $data->date;
        $amount = $data->amount;
        $currency = $data->currency;

        xmlwriter_start_element($xml, "TransferInfo");

        xmlwriter_start_element($xml, "Reference");
        xmlwriter_text($xml, $reference);
        xmlwriter_end_element($xml);

        xmlwriter_start_element($xml, "Date");
        xmlwriter_text($xml, $date);
        xmlwriter_end_element($xml);

        xmlwriter_start_element($xml, "Amount");
        xmlwriter_text($xml, $amount);
        xmlwriter_end_element($xml);

        xmlwriter_start_element($xml, "Currency");
        xmlwriter_text($xml, $currency);
        xmlwriter_end_element($xml);

        xmlwriter_end_element($xml);
    }

    private function senderInfo(XMLWriter $xml, PaymentRequestDto $data)
    {
        $senderAccountNumber = $data->senderAccountNumber;

        xmlwriter_start_element($xml, "SenderInfo");

        xmlwriter_start_element($xml, "AccountNumber");
        xmlwriter_text($xml, $senderAccountNumber);
        xmlwriter_end_element($xml);

        xmlwriter_end_element($xml);
    }

    private function receiverInfo(XMLWriter $xml, PaymentRequestDto $data)
    {
        $bankCode = $data->bankCode;
        $accountNumber = $data->receiverAccountNumber;
        $beneficiary = $data->beneficiaryName;

        xmlwriter_start_element($xml, "ReceiverInfo");

        xmlwriter_start_element($xml, "BankCode");
        xmlwriter_text($xml, $bankCode);
        xmlwriter_end_element($xml);

        xmlwriter_start_element($xml, "AccountNumber");
        xmlwriter_text($xml, $accountNumber);
        xmlwriter_end_element($xml);

        xmlwriter_start_element($xml, "BeneficiaryName");
        xmlwriter_text($xml, $beneficiary);
        xmlwriter_end_element($xml);

        xmlwriter_end_element($xml);
    }

    private function notes(XMLWriter $xml, PaymentRequestDto $data)
    {
        $notes = $data->notes;

        if (empty($notes)) {
            return;
        }

        xmlwriter_start_element($xml, "Notes");
        foreach ($notes as $note) {
            xmlwriter_start_element($xml, "Note");
            xmlwriter_text($xml, $note);
            xmlwriter_end_element($xml);
        }
        xmlwriter_end_element($xml);
    }

    private function paymentType(XMLWriter $xml, PaymentRequestDto $data)
    {
        $paymentType = $data->paymentType;

        if ($paymentType == 99) {
            return;
        }

        xmlwriter_start_element($xml, "PaymentType");
        xmlwriter_text($xml, $paymentType);
        xmlwriter_end_element($xml);
    }

    private function ChargeDetails(XMLWriter $xml, PaymentRequestDto $data)
    {
        $chargeDetails = $data->chargeDetails;

        if ($chargeDetails == "SHA") {
            return;
        }

        xmlwriter_start_element($xml, "ChargeDetails");
        xmlwriter_text($xml, $chargeDetails);
        xmlwriter_end_element($xml);
    }
}
