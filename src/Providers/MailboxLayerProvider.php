<?php

namespace AshAllenDesign\MailboxLayer\Providers;

use AshAllenDesign\MailboxLayer\Classes\MailboxLayer;
use Illuminate\Support\ServiceProvider;

class MailboxLayerProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/mailbox-layer.php', 'mailbox-layer');

        $this->app->bind('mailbox-layer', function ($app) {
            return new MailboxLayer(config('mailbox-layer.api_key'));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Config
        $this->publishes([
            __DIR__.'/../../config/mailbox-layer.php' => config_path('mailbox-layer.php'),
        ], 'mailbox-layer-config');
    }
}
