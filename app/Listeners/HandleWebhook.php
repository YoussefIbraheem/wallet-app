<?php

namespace App\Listeners;

use App\Events\WebhookReceived;
use App\Services\WebhookHandler;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleWebhook implements ShouldQueue
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
    public function handle(WebhookReceived $event): void
    {
        $this->handler->execute($event->body, $event->bankName);
    }
}
