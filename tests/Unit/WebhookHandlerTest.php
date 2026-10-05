<?php

use App\Events\TransactionParse;
use App\Models\Transaction;
use App\Services\WebhookHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

function webhookData(string $bankName, int $count = 5): Collection
{
    return Transaction::factory()
        ->bank($bankName)
        ->count($count)
        ->make();
}

test('Handler can handle Paytech format', function () {
    $data = webhookData(PAYTECH);
    $rawData = $data->pluck('raw_line')->implode("\n");

    (new WebhookHandler)->execute($rawData, PAYTECH);

    $this->assertDatabaseCount('transactions', 5);
    $this->assertDatabaseHas('transactions', $data->except('webhook_id')->toArray());
});

test('Handler can handle Acme format', function () {
    $data = webhookData(ACME);
    $rawData = $data->pluck('raw_line')->implode("\n");

    (new WebhookHandler)->execute($rawData, ACME);

    $this->assertDatabaseCount('transactions', 5);
    $this->assertDatabaseHas('transactions', $data->except('webhook_id')->toArray());
});

test('Handler can remove duplicates in Paytech format', function () {
    $data = webhookData(PAYTECH, 1);
    $rawLine = $data->pluck('raw_line')[0];
    $rawData = implode("\n", [
        $rawLine,
        $rawLine,
        $rawLine,
    ]);

    (new WebhookHandler)->execute($rawData, PAYTECH);
    $this->assertDatabaseCount('transactions', 1);
    $this->assertDatabaseHas('transactions', $data->except('webhook_id')->toArray());
});

test('Handler can remove duplicates in Acme format', function () {
    $data = webhookData(ACME, 1);
    $rawLine = $data->pluck('raw_line')[0];
    $rawData = implode("\n", [
        $rawLine,
        $rawLine,
        $rawLine,
    ]);

    (new WebhookHandler)->execute($rawData, ACME);
    $this->assertDatabaseCount('transactions', 1);
    $this->assertDatabaseHas('transactions', $data->except('webhook_id')->toArray());
});

test('Handler Dispatch TransactionParser in Paytech', function () {
    Event::fake([
        TransactionParse::class,
    ]);
    $data = webhookData(PAYTECH);
    $rawData = $data->pluck('raw_line')->implode("\n");

    (new WebhookHandler)->execute($rawData, PAYTECH);

    Event::assertDispatched(
        TransactionParse::class,
        fn ($event) => Transaction::query()->where('webhook_id', $event->webhookId)->count() === 5
    );
});

test('Handler Dispatch TransactionParser in Acme', function () {
    Event::fake([
        TransactionParse::class,
    ]);
    $data = webhookData(ACME);
    $rawData = $data->pluck('raw_line')->implode("\n");

    (new WebhookHandler)->execute($rawData, ACME);

    Event::assertDispatched(
        TransactionParse::class,
        fn ($event) => Transaction::query()->where('webhook_id', $event->webhookId)->count() === 5
    );
});
