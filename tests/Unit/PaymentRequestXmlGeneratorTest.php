<?php

use App\Services\PaymentRequestXmlGenerator;


beforeEach(function () {
    $this->generator = new PaymentRequestXmlGenerator();
});


it('includes all required elements', function () {
    $request = paymentRequest([
        'notes' => ['Lorem Epsum', 'Dolor Sit Amet'],
        'paymentType' => '421',
        'chargeDetails' => 'RB',
    ]);

    $xml = $this->generator->execute($request);

    expect($xml)
        ->toContain('<?xml version="1.0" encoding="UTF-8"?>')
        ->toContain('<PaymentRequestMessage>')

        ->toContain('<TransferInfo>')
        ->toContain("<Reference>{$request->reference}</Reference>")
        ->toContain("<Date>{$request->date}</Date>")
        ->toContain("<Amount>{$request->amount}</Amount>")
        ->toContain("<Currency>{$request->currency}</Currency>")
        ->toContain('</TransferInfo>')

        ->toContain('<SenderInfo>')
        ->toContain("<AccountNumber>{$request->senderAccountNumber}</AccountNumber>")
        ->toContain('</SenderInfo>')

        ->toContain('<ReceiverInfo>')
        ->toContain("<BankCode>{$request->bankCode}</BankCode>")
        ->toContain("<AccountNumber>{$request->receiverAccountNumber}</AccountNumber>")
        ->toContain("<BeneficiaryName>{$request->beneficiaryName}</BeneficiaryName>")
        ->toContain('</ReceiverInfo>')

        ->toContain('<Notes>')
        ->toContain('<Note>Lorem Epsum</Note>')
        ->toContain('<Note>Dolor Sit Amet</Note>')
        ->toContain('</Notes>')

        ->toContain('<PaymentType>421</PaymentType>')
        ->toContain('<ChargeDetails>RB</ChargeDetails>')

        ->toContain('</PaymentRequestMessage>');
});

it('omits notes when notes are empty', function () {
    $request = paymentRequest([
        'notes' => [],
    ]);

    $xml = $this->generator->execute($request);

    expect($xml)->not->toContain('<Notes>');
});

it('omits payment type when payment type is 99', function () {
    $request = paymentRequest([
        'paymentType' => '99',
    ]);

    $xml = $this->generator->execute($request);

    expect($xml)->not->toContain('<PaymentType>');
});

it('omits charge details when charge details is SHA', function () {
    $request = paymentRequest([
        'chargeDetails' => 'SHA',
    ]);

    $xml = $this->generator->execute($request);

    expect($xml)->not->toContain('<ChargeDetails>');
});
