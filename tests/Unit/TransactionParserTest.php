<?php

use App\BankParser\Acme;
use App\BankParser\BankParserRegistry;
use App\BankParser\PayTech;
use App\Models\ParsedTransaction;
use App\Models\Transaction;
use App\Providers\BankParserServiceProvider;
use App\Services\TransactionParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->app->singleton(BankParserRegistry::class, function ($app) {
        $registry = new BankParserRegistry();
        $registry->register($app->make(PayTech::class));
        $registry->register($app->make(Acme::class));
        return $registry;
    });
});


function transactionData(string $bankName, int $count = 5): Collection
{
    return Transaction::factory()
        ->bank($bankName)
        ->count($count)
        ->create();
}


test("Parser can handle paytech transactions", function () {
    $transactions = transactionData(PAYTECH);

    $webhookId = $transactions->first()->webhook_id;

    $registry = $this->app->make(BankParserRegistry::class);

    (new TransactionParser($registry))->execute($webhookId, PAYTECH);

    $dbTransactions = ParsedTransaction::all();

    //dd($transactions->toArray(),$dbTransactions->toArray());

    $this->assertDatabaseCount("parsed_transactions", 5);
});

test("Parser can handle acme transactions", function () {
    $transactions = transactionData(ACME);

    $webhookId = $transactions->first()->webhook_id;

    $registry = $this->app->make(BankParserRegistry::class);

    (new TransactionParser($registry))->execute($webhookId, ACME);


    $this->assertDatabaseCount("parsed_transactions", 5);
});

test("Parser will fail paytech transactions that are not formatted", function () {
    $transactions = transactionData(ACME,1);

    $webhookId = $transactions->first()->webhook_id;

    $registry = $this->app->make(BankParserRegistry::class);

    (new TransactionParser($registry))->execute($webhookId, PAYTECH);

    $this->assertDatabaseCount("parsed_transactions", 0);
    $this->assertDatabaseCount("transactions",1);
    $this->assertDatabaseHas("transactions",["status"=>"failed"]);

});


test("Parser will fail acme transactions that are not formatted", function () {
    $transactions = transactionData(PAYTECH,1);

    $webhookId = $transactions->first()->webhook_id;

    $registry = $this->app->make(BankParserRegistry::class);

    (new TransactionParser($registry))->execute($webhookId, ACME);

    $this->assertDatabaseCount("parsed_transactions", 0);
    $this->assertDatabaseCount("transactions",1);
    $this->assertDatabaseHas("transactions",["status"=>"failed"]);

});

