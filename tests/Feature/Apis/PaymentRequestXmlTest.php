<?php

use App\Services\PaymentRequestXmlGenerator;
use Database\Factories\PaymentRequestFactory;

beforeEach(function () {
    $this->generator = new PaymentRequestXmlGenerator;
});

it('generates payment request xml', function () {
    $data = (new PaymentRequestFactory)->make([
        'notes' => ['Lorem Epsum', 'Dolor Sit Amet'],
        'paymentType' => '421',
        'chargeDetails' => 'RB',
    ]);

    $response = $this->postJson('/api/payment', [
        'reference' => $data['reference'],
        'date' => $data['date'],
        'amount' => $data['amount'],
        'currency' => $data['currency'],
        'sender_account_number' => $data['senderAccountNumber'],
        'bank_code' => $data['bankCode'],
        'receiver_account_number' => $data['receiverAccountNumber'],
        'beneficiary_name' => $data['beneficiaryName'],
        'notes' => $data['notes'],
        'payment_type' => $data['paymentType'],
        'charge_details' => $data['chargeDetails'],
    ], headers: ['Accept' => 'application/xml']);

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml');

    expect($response->getContent())
        ->toContain('<PaymentRequestMessage>')
        ->toContain("<Reference>{$data['reference']}</Reference>")
        ->toContain("<Amount>{$data['amount']}</Amount>")
        ->toContain("<Currency>{$data['currency']}</Currency>")
        ->toContain("<PaymentType>{$data['paymentType']}</PaymentType>")
        ->toContain("<ChargeDetails>{$data['chargeDetails']}</ChargeDetails>");
});

it('omits optional elements according to their default values', function () {
    $data = (new PaymentRequestFactory)->make([
        'notes' => [],
        'paymentType' => '99',
        'chargeDetails' => 'SHA',
    ]);

    $response = $this->postJson('/api/payment', [
        'reference' => $data['reference'],
        'date' => $data['date'],
        'amount' => $data['amount'],
        'currency' => $data['currency'],
        'sender_account_number' => $data['senderAccountNumber'],
        'bank_code' => $data['bankCode'],
        'receiver_account_number' => $data['receiverAccountNumber'],
        'beneficiary_name' => $data['beneficiaryName'],
        'notes' => $data['notes'],
        'payment_type' => $data['paymentType'],
        'charge_details' => $data['chargeDetails'],
    ]);

    $response->assertOk();

    expect($response->getContent())
        ->not->toContain('<Notes>')
        ->not->toContain('<PaymentType>')
        ->not->toContain('<ChargeDetails>');
});
