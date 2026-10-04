<?php

namespace App\Listeners;

use App\BankParser\BankParserRegistry;
use App\Enums\Bank;
use App\Events\BankStatementReceived;
use App\Services\WebhookHandler;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProcessBankTransaction implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(private WebhookHandler $handler)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(BankStatementReceived $event): void
    {
        $this->handler->execute($event->body, $event->bankName);
    }
}
