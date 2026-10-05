<?php

use App\Events\TransactionParse;
use App\Events\WebhookReceived;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

const ACME_WEBHOOK = '/api/webhook/acme/payment';

function acmeWebhookData(int $count = 5): string
{
    return Transaction::factory()
        ->bank('acme')
        ->count($count)
        ->make()
        ->pluck('raw_line')
        ->implode("\n");
}

test('acme webhook returns 204 for valid request', function () {
    $rawData = acmeWebhookData();

    $response = $this->call(
        method: 'POST',
        uri: ACME_WEBHOOK,
        parameters: [],
        cookies: [],
        files: [],
        server: ['CONTENT_TYPE' => 'text/plain'],
        content: $rawData
    );

    $response->assertNoContent();
});

test('acme webhook dispatches WebhookReceived with correct data', function () {
    Event::fake();

    $rawData = acmeWebhookData();

    $this->call(
        method: 'POST',
        uri: ACME_WEBHOOK,
        parameters: [],
        cookies: [],
        files: [],
        server: ['CONTENT_TYPE' => 'text/plain'],
        content: $rawData
    );

    Event::assertDispatched(
        WebhookReceived::class,
        fn ($event) => $event->body === $rawData &&
            $event->bankName === 'acme'
    );
});

test('acme webhook returns 400 when request body is empty', function () {
    $response = $this->call(
        method: 'POST',
        uri: ACME_WEBHOOK,
        parameters: [],
        cookies: [],
        files: [],
        server: ['CONTENT_TYPE' => 'text/plain'],
        content: ''
    );

    $response->assertStatus(400);

    $response->assertSee('Empty data received');
});

test('acme webhook stores all unique transactions', function () {
    $rawData = acmeWebhookData(5);

    $this->call(
        method: 'POST',
        uri: ACME_WEBHOOK,
        parameters: [],
        cookies: [],
        files: [],
        server: ['CONTENT_TYPE' => 'text/plain'],
        content: $rawData
    );

    $this->assertDatabaseCount('transactions', 5);
});

test('acme webhook ignores duplicate transactions', function () {
    $transaction = Transaction::factory()
        ->bank('acme')
        ->make();

    $rawData = implode("\n", [
        $transaction->raw_line,
        $transaction->raw_line,
        $transaction->raw_line,
    ]);

    $this->call(
        method: 'POST',
        uri: ACME_WEBHOOK,
        parameters: [],
        cookies: [],
        files: [],
        server: ['CONTENT_TYPE' => 'text/plain'],
        content: $rawData
    );

    $this->assertDatabaseCount('transactions', 1);
});

test('acme webhook dispatches transaction parsing after storing the batch', function () {
    Event::fake([
        TransactionParse::class,
    ]);

    $rawData = acmeWebhookData(5);

    $this->call(
        method: 'POST',
        uri: ACME_WEBHOOK,
        parameters: [],
        cookies: [],
        files: [],
        server: ['CONTENT_TYPE' => 'text/plain'],
        content: $rawData
    );

    $this->assertDatabaseCount('transactions', 5);

    Event::assertDispatched(
        TransactionParse::class,
        fn ($event) => Transaction::where('webhook_id', $event->webhookId)->count() === 5
    );
});

test('Acme can handle 1000 transactions in a single webhook request', function () {
    $rawData = acmeWebhookData(1000);

    $response = $this->call(
        method: 'POST',
        uri: ACME_WEBHOOK,
        parameters: [],
        cookies: [],
        files: [],
        server: ['CONTENT_TYPE' => 'text/plain'],
        content: $rawData
    );

    $response->assertNoContent();

    $this->assertDatabaseCount('transactions', 1000);
});
