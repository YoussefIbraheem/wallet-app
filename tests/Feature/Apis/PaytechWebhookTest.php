<?php

use App\Events\TransactionParse;
use App\Events\WebhookReceived;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

const PAYTECH_WEBHOOK = '/api/webhook/paytech/payment';

function paytechWebhookRawData(int $count = 5): string
{
    return Transaction::factory()
        ->bank('paytech')
        ->count($count)
        ->make()
        ->pluck('raw_line')
        ->implode("\n");
}

test('paytech webhook returns 204 for valid request', function () {
    $rawData = paytechWebhookRawData();

    $response = $this->call(
        method: 'POST',
        uri: PAYTECH_WEBHOOK,
        parameters: [],
        cookies: [],
        files: [],
        server: ['CONTENT_TYPE' => 'text/plain'],
        content: $rawData
    );

    $response->assertNoContent();
});

test('paytech webhook dispatches WebhookReceived with correct data', function () {
    Event::fake();

    $rawData = paytechWebhookRawData();

    $this->call(
        method: 'POST',
        uri: PAYTECH_WEBHOOK,
        parameters: [],
        cookies: [],
        files: [],
        server: ['CONTENT_TYPE' => 'text/plain'],
        content: $rawData
    );

    Event::assertDispatched(
        WebhookReceived::class,
        fn ($event) => $event->body === $rawData &&
            $event->bankName === 'paytech'
    );
});

test('paytech webhook returns 400 when request body is empty', function () {
    $response = $this->call(
        method: 'POST',
        uri: PAYTECH_WEBHOOK,
        parameters: [],
        cookies: [],
        files: [],
        server: ['CONTENT_TYPE' => 'text/plain'],
        content: ''
    );

    $response->assertStatus(400);

    $response->assertSee('Empty data received');
});

test('paytech webhook stores all unique transactions', function () {
    $rawData = paytechWebhookRawData(5);

    $this->call(
        method: 'POST',
        uri: PAYTECH_WEBHOOK,
        parameters: [],
        cookies: [],
        files: [],
        server: ['CONTENT_TYPE' => 'text/plain'],
        content: $rawData
    );

    $this->assertDatabaseCount('transactions', 5);
});

test('paytech webhook ignores duplicate transactions', function () {
    $transaction = Transaction::factory()
        ->bank('paytech')
        ->make();

    $rawData = implode("\n", [
        $transaction->raw_line,
        $transaction->raw_line,
        $transaction->raw_line,
    ]);

    $this->call(
        method: 'POST',
        uri: PAYTECH_WEBHOOK,
        parameters: [],
        cookies: [],
        files: [],
        server: ['CONTENT_TYPE' => 'text/plain'],
        content: $rawData
    );

    $this->assertDatabaseCount('transactions', 1);
});

test('paytech webhook dispatches transaction parsing after storing the batch', function () {
    Event::fake([
        TransactionParse::class,
    ]);

    $rawData = paytechWebhookRawData(5);

    $this->call(
        method: 'POST',
        uri: PAYTECH_WEBHOOK,
        parameters: [],
        cookies: [],
        files: [],
        server: ['CONTENT_TYPE' => 'text/plain'],
        content: $rawData
    );

    $this->assertDatabaseCount('transactions', 5);

    Event::assertDispatched(
        TransactionParse::class,
        fn ($event) => Transaction::query()->where('webhook_id', $event->webhookId)->count() === 5
    );
});

test('paytech handles metadata correctly', function () {
    Event::fake([
        TransactionParse::class,
    ]);

    $rawData = paytechWebhookRawData(5);

    $this->call(
        method: 'POST',
        uri: PAYTECH_WEBHOOK,
        parameters: [],
        cookies: [],
        files: [],
        server: ['CONTENT_TYPE' => 'text/plain'],
        content: $rawData
    );

    $this->assertDatabaseCount('transactions', 5);

    Event::assertDispatched(TransactionParse::class, function ($event) {
        $metadata = collect();
        Transaction::query()->where('webhook_id', $event->webhookId)->get()->each(function ($transaction) use ($metadata) {
            $metadata->push($transaction->transactionMetadata);
        });

        return $metadata->count() === 5;
    });
});

test('Paytech can handle 1000 transactions in a single webhook request', function () {
    $rawData = paytechWebhookRawData(1000);

    $response = $this->call(
        method: 'POST',
        uri: PAYTECH_WEBHOOK,
        parameters: [],
        cookies: [],
        files: [],
        server: ['CONTENT_TYPE' => 'text/plain'],
        content: $rawData
    );

    $response->assertNoContent();

    $this->assertDatabaseCount('transactions', 1000);
});
