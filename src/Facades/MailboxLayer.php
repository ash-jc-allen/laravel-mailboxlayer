<?php

namespace AshAllenDesign\MailboxLayer\Facades;

use AshAllenDesign\MailboxLayer\Classes\ValidationResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ValidationResult check(string $emailAddress)
 * @method static Collection checkMany(array $emailAddresses)
 * @method static self shouldCache(bool $shouldCache = true)
 * @method static self fresh(bool $fresh = true)
 * @method static self withHttps(bool $https = true)
 * @method static self withSmtpCheck(bool $smtpCheck = true)
 *
 * @see \AshAllenDesign\MailboxLayer\Classes\MailboxLayer
 */
class MailboxLayer extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return 'mailbox-layer';
    }
}
