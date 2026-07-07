<?php

namespace App\Providers;

use App\Services\Notification\LogSmsGateway;
use App\Services\Notification\SmsGatewayInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // No SMS gateway (Twilio/etc.) is configured for this app yet — bind
        // the logging stub. Swap this binding for a real gateway when one is
        // procured; nothing else in the Notification Center needs to change.
        $this->app->bind(SmsGatewayInterface::class, LogSmsGateway::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
