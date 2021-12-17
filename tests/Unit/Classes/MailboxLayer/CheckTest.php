<?php

namespace AshAllenDesign\MailboxLayer\Tests\Unit\Classes\MailboxLayer;

use AshAllenDesign\MailboxLayer\Classes\MailboxLayer;
use AshAllenDesign\MailboxLayer\Classes\ValidationResult;
use AshAllenDesign\MailboxLayer\Exceptions\MailboxLayerException;
use AshAllenDesign\MailboxLayer\Facades\MailboxLayer as MailboxLayerFacade;
use AshAllenDesign\MailboxLayer\Tests\Unit\TestCase;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CheckTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(now());
    }

    /** @test */
    public function result_is_returned_from_cache_if_fresh_is_set_to_false_and_it_exists_in_the_cache()
    {
        // Set a cached value that we can get.
        Cache::shouldReceive('get')
            ->once()
            ->withArgs(['mailboxlayer_result_mail@ashallendesign.co.uk'])
            ->andReturn($this->responseStructure());

        // Assert that the HTTP client is never called.
        Http::shouldReceive('get')->never();

        $mailboxLayer = new MailboxLayer(123);

        $result = $mailboxLayer->check('mail@ashallendesign.co.uk');

        $this->assertValidationResultIsCorrect($result);
    }

    /** @test */
    public function result_is_returned_from_the_api_if_fresh_is_set_to_false_but_it_does_not_exist_in_the_cache()
    {
        // Set a cached value that we can get.
        Cache::shouldReceive('get')
            ->once()
            ->withArgs(['mailboxlayer_result_mail@ashallendesign.co.uk'])
            ->andReturnNull();

        // Mock the API response.
        Http::fake(function () {
            return Http::response($this->responseStructure());
        });

        $mailboxLayer = new MailboxLayer(123);

        $result = $mailboxLayer->check('mail@ashallendesign.co.uk');

        $this->assertValidationResultIsCorrect($result);

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://apilayer.net/api/check?access_key=123&email=mail%40ashallendesign.co.uk&smtp=1';
        });
    }

    /** @test */
    public function result_is_returned_from_the_api_if_fresh_is_set_to_true()
    {
        Cache::shouldReceive('forget')
            ->withArgs(['mailboxlayer_result_mail@ashallendesign.co.uk'])
            ->once()
            ->andReturnTrue();

        Cache::shouldReceive('get')->never();

        Cache::shouldReceive('forever')->never();

        // Mock the API response.
        Http::fake(function () {
            return Http::response($this->responseStructure());
        });

        $mailboxLayer = new MailboxLayer(123);

        $result = $mailboxLayer->fresh()->check('mail@ashallendesign.co.uk');

        $this->assertValidationResultIsCorrect($result);

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://apilayer.net/api/check?access_key=123&email=mail%40ashallendesign.co.uk&smtp=1';
        });
    }

    /** @test */
    public function result_is_cached_if_should_bust_cache_is_set_to_true()
    {
        // Mock the API response.
        Http::fake(function () {
            return Http::response($this->responseStructure());
        });

        Cache::shouldReceive('get')->once()->andReturnNull();

        Cache::shouldReceive('forever')
            ->withArgs([
                'mailboxlayer_result_mail@ashallendesign.co.uk',
                array_merge($this->responseStructure(), ['validatedAt' => now()]),
            ])
            ->once()
            ->andReturnTrue();

        $mailboxLayer = new MailboxLayer(123);

        $result = $mailboxLayer->shouldCache()->check('mail@ashallendesign.co.uk');

        $this->assertValidationResultIsCorrect($result);

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://apilayer.net/api/check?access_key=123&email=mail%40ashallendesign.co.uk&smtp=1';
        });
    }

    /** @test */
    public function email_can_be_validated_without_the_smtp_check()
    {
        // Mock the API response.
        Http::fake(function () {
            return Http::response($this->responseStructure());
        });

        $mailboxLayer = new MailboxLayer(123);

        $result = $mailboxLayer->withSmtpCheck(false)->check('mail@ashallendesign.co.uk');

        $this->assertValidationResultIsCorrect($result);

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://apilayer.net/api/check?access_key=123&email=mail%40ashallendesign.co.uk&smtp=0';
        });
    }

    /** @test */
    public function request_can_be_sent_without_using_https()
    {
        // Mock the API response.
        Http::fake(function () {
            return Http::response($this->responseStructure());
        });

        $mailboxLayer = new MailboxLayer(123);

        $result = $mailboxLayer->withHttps(false)->check('mail@ashallendesign.co.uk');

        $this->assertValidationResultIsCorrect($result);

        Http::assertSent(function (Request $request) {
            return $request->url() === 'http://apilayer.net/api/check?access_key=123&email=mail%40ashallendesign.co.uk&smtp=1';
        });
    }

    /** @test */
    public function exception_is_thrown_if_the_api_request_returns_an_error()
    {
        $this->expectException(MailboxLayerException::class);
        $this->expectExceptionCode(101);
        $this->expectExceptionMessage('You have not supplied a valid API Access Key. [Technical Support: support@apilayer.com]');

        // Mock the API response.
        Http::fake(function () {
            return Http::response($this->errorResponseStructure());
        });

        $mailboxLayer = new MailboxLayer(123);

        $result = $mailboxLayer->withHttps(false)->check('mail@ashallendesign.co.uk');

        $this->assertValidationResultIsCorrect($result);

        Http::assertSent(function (Request $request) {
            return $request->url() === 'http://apilayer.net/api/check?access_key=123&email=mail%40ashallendesign.co.uk&smtp=1';
        });
    }

    /** @test */
    public function validation_can_be_carried_out_using_the_facade()
    {
        // Set the API key in the config so that it can be used when
        // creating the facade.
        config(['mailbox-layer.api_key' => 123]);

        // Mock the API response.
        Http::fake(function () {
            return Http::response($this->responseStructure());
        });

        $result = MailboxLayerFacade::withHttps(false)->check('mail@ashallendesign.co.uk');

        $this->assertValidationResultIsCorrect($result);

        Http::assertSent(function (Request $request) {
            return $request->url() === 'http://apilayer.net/api/check?access_key=123&email=mail%40ashallendesign.co.uk&smtp=1';
        });
    }

    private function responseStructure(): array
    {
        return [
            'email'       => 'mail@ashallendesign.co.uk',
            'didYouMean'  => '',
            'user'        => 'mail',
            'domain'      => 'ashallendesign.co.uk',
            'formatValid' => true,
            'mxFound'     => true,
            'smtpCheck'   => true,
            'catchAll'    => false,
            'role'        => true,
            'disposable'  => false,
            'free'        => false,
            'score'       => 0.8,
        ];
    }

    private function errorResponseStructure(): array
    {
        return [
            'success' => false,
            'error'   => [
                'code' => '101',
                'type' => 'invalid_access_key',
                'info' => 'You have not supplied a valid API Access Key. [Technical Support: support@apilayer.com]',
            ],
        ];
    }

    private function assertValidationResultIsCorrect(ValidationResult $result): void
    {
        $this->assertSame('mail@ashallendesign.co.uk', $result->email);
        $this->assertSame('', $result->didYouMean);
        $this->assertSame('mail', $result->user);
        $this->assertSame('ashallendesign.co.uk', $result->domain);
        $this->assertTrue($result->formatValid);
        $this->assertTrue($result->smtpCheck);
        $this->assertTrue($result->role);
        $this->assertFalse($result->disposable);
        $this->assertFalse($result->free);
        $this->assertSame(0.8, $result->score);
        $this->assertEquals(now(), $result->validatedAt);
    }
}
