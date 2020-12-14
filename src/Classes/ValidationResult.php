<?php

namespace AshAllenDesign\MailboxLayer\Classes;

use Illuminate\Support\Str;

class ValidationResult
{
    /**
     * The email address that the validation was carried
     * out on.
     *
     * @var string
     */
    public $email;

    /**
     * A suggested email address in case a typo was detected.
     *
     * @var string
     */
    public $didYouMean;

    /**
     * The local part of the email address. Example:
     * 'mail' in 'mail@ashallendesign.co.uk'
     *
     * @var string
     */
    public $user;

    /**
     * The domain part of the email address. Example:
     * 'ashallendesign.co.uk' in 'mail@ashallendesign.co.uk'.
     *
     * @var string
     */
    public $domain;

    /**
     * Whether or not the syntax of the requested email is
     * valid.
     *
     * @var bool
     */
    public $formatValid;

    /**
     * Whether or not the MX records for the requested
     * domain could be found.
     *
     * @var bool
     */
    public $mxFound;

    /**
     * Whether or not the SMTP check of the requested email
     * address succeeded.
     *
     * @var bool
     */
    public $smtpCheck;

    /**
     * Whether or not the requested email address is found
     * to be part of a catch-all mailbox.
     *
     * @var bool
     */
    public $catchAll;

    /**
     * Whether or not the requested email is a role email
     * address. Example: 'support@ashallendesign.co.uk'
     *
     * @var bool
     */
    public $role;

    /**
     * Whether or not the requested email is disposable.
     * Example: 'hello@mailinator.com'.
     *
     * @var bool
     */
    public $disposable;

    /**
     * Whether or not the requested email is a free email
     * address.
     *
     * @var bool
     */
    public $free;

    /**
     * A score between 0 and 1 reflecting the quality and
     * deliverability of the requested email address.
     *
     * @var float
     */
    public $score;

    /**
     * Build a new ValidationObject from the API response
     * data, set the properties and then return it.
     *
     * @param  array  $response
     * @return static
     */
    public static function makeFromResponse(array $response): self
    {
        $validationResult = new self;

        foreach($response as $fieldName => $value) {
            $objectFieldName = Str::camel($fieldName);
            $validationResult->{$objectFieldName} = $value;
        }

        return $validationResult;
    }
}