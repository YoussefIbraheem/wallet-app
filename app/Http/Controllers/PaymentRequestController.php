<?php

namespace App\Http\Controllers;

use App\DTOs\PaymentRequestDto;
use App\Http\Requests\PaymentRequestFormRequest;
use App\Services\PaymentRequestXmlGenerator;
use Illuminate\Http\Request;

class PaymentRequestController extends Controller
{
    /**
     * Handle the incoming request.
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

        return $xmlData;
    }
}
