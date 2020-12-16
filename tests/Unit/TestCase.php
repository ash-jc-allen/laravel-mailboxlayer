<?php

namespace AshAllenDesign\MailboxLayer\Tests\Unit;

use AshAllenDesign\MailboxLayer\Facades\MailboxLayer;
use AshAllenDesign\MailboxLayer\Providers\MailboxLayerProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * Load package service provider.
     *
     * @param $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [MailboxLayerProvider::class];
    }

    /**
     * Load package alias.
     *
     * @param Application $app
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
