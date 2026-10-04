<?php

namespace App\Listeners;


use App\Events\TransactionParse;
use App\Services\TransactionParser;
use Illuminate\Contracts\Queue\ShouldQueue;

class ParseTransaction implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(private TransactionParser $parser)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TransactionParse $event): void
    {
        $this->parser->execute($event->webhookId, $event->bankName);
    }
}
