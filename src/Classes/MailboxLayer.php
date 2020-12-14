<?php

namespace AshAllenDesign\MailboxLayer\Classes;

use AshAllenDesign\MailboxLayer\Exceptions\MailboxLayerException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
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
     * Whether or not to run the SMTP check for the email
     * address. Setting this to false will decrease the
     * API response time.
     *
     * @var bool
     */
    private $smtpCheck = true;

    /**
     * Whether or not the email validation result should
     * be cached.
     *
     * @var bool
     */
    private $shouldCache = false;

    /**
     * Whether or not a fresh result should be fetched from
     * the API. Setting field this to true will ignore
     * any cached values. It will also delete the
     * previously cached result if one exists.
     *
     * @var bool
     */
    private $fresh = false;

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
        $cacheKey = $this->buildCacheKey($emailAddress);

        if ($this->fresh) {
            Cache::forget($cacheKey);
        }

        if (! $this->fresh) {
            $cached = Cache::get($cacheKey);

            if ($cached) {
                $result = ValidationResult::makeFromResponse(Cache::get($cacheKey));
            }
        }

        if (! isset($result)) {
            $result = $this->fetchFromApi($emailAddress);
        }

        if ($this->shouldCache) {
            Cache::forever($cacheKey, (array)$result);
        }

        return $result;
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
     * Whether or not the email validation result should
     * be cached after it's fetched from the API.
     *
     * @param  bool  $shouldCache
     * @return $this
     */
    public function shouldCache(bool $shouldCache = true): self
    {
        $this->shouldCache = $shouldCache;

        return $this;
    }

    /**
     * Whether or not a fresh result should be fetched from
     * the API. Setting field this to true will ignore
     * any cached values. It will also delete the
     * previously cached result if one exists.
     *
     * @param  bool  $fresh
     * @return $this
     */
    public function fresh(bool $fresh = true): self
    {
        $this->fresh = $fresh;

        return $this;
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
     * Determine whether if an SMTP check should be used
     * when validating the address. By not running the
     * SMTP check, the API response time will be
     * decreased.
     *
     * @param  bool  $smtpCheck
     * @return $this
     */
    public function withSmtpCheck(bool $smtpCheck = true): self
    {
        $this->smtpCheck = $smtpCheck;

        return $this;
    }

    /**
     * Build the URL that the request will be made to.
     *
     * @param  string  $emailAddress
     * @return string
     */
    private function buildUrl(string $emailAddress): string
    {
        $protocol = $this->withHttps ? 'https://' : 'http://';

        $params = http_build_query([
            'access_key' => $this->apiKey,
            'email'      => $emailAddress,
            'smtp'       => $this->smtpCheck
        ]);

        return $protocol.self::BASE_URL.'?'.$params;
    }

    /**
     * Make a request to the API and fetch a new result.
     *
     * @param  string  $emailAddress
     * @return ValidationResult
     * @throws MailboxLayerException
     */
    private function fetchFromApi(string $emailAddress): ValidationResult
    {
        $response = Http::get($this->buildUrl($emailAddress));

        if (isset($response->json()['success']) && ! $response->json()['success']) {
            $error = $response->json()['error'];

            throw new MailboxLayerException($error['info'], $error['code']);
        }

        return ValidationResult::makeFromResponse($response->json());
    }

    /**
     * Build and return the key that will be used when
     * setting or getting the validation result from
     * the cache.
     *
     * @param  string  $emailAddress
     * @return string
     */
    private function buildCacheKey(string $emailAddress): string
    {
        return 'mailboxlayer_result_'.$emailAddress;
    }
}