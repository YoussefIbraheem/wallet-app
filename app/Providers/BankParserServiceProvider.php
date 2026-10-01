<?php

namespace App\Providers;

use App\BankParser\BankParserRegistry;
use App\BankParser\PayTech;
use Illuminate\Support\ServiceProvider;

class BankParserServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(BankParserRegistry::class, function ($app) {
            $registery = new BankParserRegistry();
            $registery->register($app->make(PayTech::class));

            return $registery;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
