<?php

use App\Events\BankStatementReceived;
use App\Events\TransactionParse;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

const PAYTECH_WEBHOOK = '/api/webhook/paytech/payment';

function paytechWebhookData(int $count = 5): string
{
    return Transaction::factory()
        ->bank('paytech')
        ->count($count)
        ->make()
        ->pluck('raw_line')
        ->implode("\n");
}

test('paytech webhook returns 204 for valid request', function () {
    $rawData = paytechWebhookData();

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

test('paytech webhook dispatches BankStatementReceived with correct data', function () {
    Event::fake();

    $rawData = paytechWebhookData();

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
        BankStatementReceived::class,
        fn ($event) =>
            $event->body === $rawData &&
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
    $rawData = paytechWebhookData(5);

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

    $rawData = paytechWebhookData(5);

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
        fn ($event) =>
            Transaction::query()->where('webhook_id', $event->webhookId)->count() === 5
    );
});
