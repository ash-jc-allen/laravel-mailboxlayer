<?php

namespace AshAllenDesign\MailboxLayer\Classes;

use AshAllenDesign\MailboxLayer\Exceptions\MailboxLayerException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class MailboxLayer
{
    private const BASE_URL = 'apilayer.net/api/check';

    /**
     * The Mailbox Layer API key that is used when making
     * requests.
     *
     * @var string
     */
    private $apiKey;

    /**
     * Whether or not to use HTTPS when making the request.
     *
     * @var bool
     */
    private $withHttps = true;

    /**
     * MailboxLayer constructor.
     *
     * @param  string  $apiKey
     */
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Run a validation check against the email address.
     * Once this has been done, return the results in
     * a ValidationObject.
     *
     * @param  string  $emailAddress
     * @return ValidationResult
     * @throws MailboxLayerException
     */
    public function check(string $emailAddress): ValidationResult
    {
        $response = Http::get($this->buildUrl($emailAddress));

        if (isset($response->json()['success']) && ! $response->json()['success']) {
            $error = $response->json()['error'];

            throw new MailboxLayerException($error['info'], $error['code']);
        }

        return ValidationResult::makeFromResponse($response->json());
    }

    /**
     * Run validation checks on more than one email address.
     * Add each of the results to a Collection and then
     * return it.
     *
     * @param  array  $emailAddresses
     * @return Collection
     * @throws MailboxLayerException
     */
    public function checkMany(array $emailAddresses): Collection
    {
        $results = collect();

        foreach ($emailAddresses as $email) {
            $results->push($this->check($email));
        }

        return $results;
    }

    /**
     * Determine whether if HTTPS should be used when
     * making the API request.
     *
     * @param  bool  $https
     * @return $this
     */
    public function withHttps(bool $https = true): self
    {
        $this->withHttps = $https;

        return $this;
    }

    /**
     * Build the URL that the request will be made to.
     *
     * @param  string  $email
     * @return string
     */
    private function buildUrl(string $email): string
    {
        $protocol = $this->withHttps ? 'https://' : 'http://';

        $params = http_build_query([
            'access_key' => $this->apiKey,
            'email'      => $email
        ]);

        return $protocol.self::BASE_URL.'?'.$params;
    }
}