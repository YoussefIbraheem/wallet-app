<?php

namespace App\DTOs;

use DateTimeInterface;

final readonly class PaymentRequestDto
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $reference,
        public string $date,
        public string $amount,
        public string $currency,
        public string $senderAccountNumber,
        public string $bankCode,
        public string $receiverAccountNumber,
        public string $beneficiaryName,
        public array $notes = [],
        public int $paymentType = 99,
        public string $chargeDetails = 'SHA',
    ) {}
}
