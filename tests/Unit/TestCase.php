<?php

namespace AshAllenDesign\MailboxLayer\Tests\Unit;

use AshAllenDesign\MailboxLayer\Facades\MailboxLayer;
use AshAllenDesign\MailboxLayer\Providers\MailboxLayerProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [MailboxLayerProvider::class];
    }

    /**
     * Get package aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'mailbox-layer' => MailboxLayer::class,
        ];
    }
}
