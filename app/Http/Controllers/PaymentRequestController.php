<?php

namespace App\Http\Controllers;

use App\DTOs\PaymentRequestDto;
use App\Http\Requests\PaymentRequestFormRequest;
use App\Services\PaymentRequestXmlGenerator;
use Knuckles\Scribe\Attributes\Header;
use Illuminate\Http\Request;

class PaymentRequestController extends Controller
{
    /**
     * Payment Request Sender
     *
     * An endpoint used to send transactions to banks that are of type Send Money
     *
     * @bodyParam reference string required Tranasaction reference. Example:e0f4763d-28ea-42d4-ac1c-c4013c242105
     * @bodyParam date string required Transaction date. Example: 2025-02-25 06:33:00+03
     * @bodyParam amount string required Transaction amount. Example: 177.39
     * @bodyParam currency string required  Transaction currency. Example: SAR
     * @bodyParam senderAccountNumber string required  Transaction sender account number. Example: SA6980000204608016212908
     * @bodyParam bankCode string required  Teceiever bank code. Example: FDCSSARI
     * @bodyParam receiverAccountNumber string required  transaction receiver account number. Example: SA6980000204608016211111
     * @bodyParam beneficiaryName string required  transaction beneficiary name. Example: Jane Doe
     * @bodyParam notes string required  transaction notes. Example: Dolor Sit Amet
     * @bodyParam paymentType string required  transaction ayment type. Example: 421
     * @bodyParam chargeDetails string required  transaction charge details. Example: RB
     *
     * @response 200
     * <?xml version="1.0" encoding="UTF-8"?>
     * <PaymentRequestMessage>
     *     <TransferInfo>
     *         <Reference>e0f4763d-28ea-42d4-ac1c-c4013c242105</Reference>
     *         <Date>2025-02-25 06:33:00+03</Date>
     *         <Amount>177.39</Amount>
     *         <Currency>SAR</Currency>
     *     </TransferInfo>
     *     <SenderInfo>
     *         <AccountNumber>SA6980000204608016212908</AccountNumber>
     *     </SenderInfo>
     *     <ReceiverInfo>
     *         <BankCode>FDCSSARI</BankCode>
     *         <AccountNumber>SA6980000204608016211111</AccountNumber>
     *         <BeneficiaryName>Jane Doe</BeneficiaryName>
     *     </ReceiverInfo>
     *     <Notes>
     *         <Note>Lorem Epsum</Note>
     *         <Note>Dolor Sit Amet</Note>
     *     </Notes>
     *     <PaymentType>421</PaymentType>
     *     <ChargeDetails>RB</ChargeDetails>
     * </PaymentRequestMessage>
     */
    public function __invoke(Request $request, PaymentRequestFormRequest $request_form)
    {
        $validatedData = $request_form->validated();
        $data = new PaymentRequestDto(
            reference: $validatedData["reference"],
            date: $validatedData["date"],
            amount: $validatedData["amount"],
            currency: $validatedData["currency"],
            senderAccountNumber: $validatedData["sender_account_number"],
            bankCode: $validatedData["bank_code"],
            receiverAccountNumber: $validatedData["receiver_account_number"],
            beneficiaryName: $validatedData["beneficiary_name"],
            notes: $validatedData["notes"],
            paymentType: $validatedData["payment_type"],
            chargeDetails: $validatedData["charge_details"],

        );

        $xmlData = (new PaymentRequestXmlGenerator())->execute($data);

        return response($xmlData, 200)
            ->header('Content-Type', 'application/xml');
    }
}
