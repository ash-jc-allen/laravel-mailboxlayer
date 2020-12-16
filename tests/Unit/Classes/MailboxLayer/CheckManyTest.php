<?php

namespace AshAllenDesign\MailboxLayer\Tests\Unit\Classes\MailboxLayer;

use AshAllenDesign\MailboxLayer\Classes\MailboxLayer;
use AshAllenDesign\MailboxLayer\Tests\Unit\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CheckManyTest extends TestCase
{
    /** @test */
    public function many_emails_can_be_validated_via_the_api()
    {
        Carbon::setTestNow(now());

        Cache::shouldReceive('get')
            ->once()
            ->withArgs(['mailboxlayer_result_mail@ashallendesign.co.uk'])
            ->andReturnNull();

        Cache::shouldReceive('get')
            ->once()
            ->withArgs(['mailboxlayer_result_support1@ashallendesign.co.uk'])
            ->andReturnNull();

        Http::fake([
            'https://apilayer.net/api/check?access_key=123&email=mail%40ashallendesign.co.uk&smtp=1'     => Http::response([
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
            ]),
            'https://apilayer.net/api/check?access_key=123&email=support1%40ashallendesign.co.uk&smtp=1' => Http::response([
                'email'       => 'support1@ashallendesign.co.uk',
                'didYouMean'  => 'support@ashallendesign.co.uk',
                'user'        => 'support1',
                'domain'      => 'ashallendesign.co.uk',
                'formatValid' => false,
                'mxFound'     => false,
                'smtpCheck'   => false,
                'catchAll'    => true,
                'role'        => false,
                'disposable'  => true,
                'free'        => true,
                'score'       => 0.7,
            ]),
        ]);

        $mailboxLayer = new MailboxLayer(123);

        $result = $mailboxLayer->checkMany(['mail@ashallendesign.co.uk', 'support1@ashallendesign.co.uk']);
        $this->assertEquals(Collection::class, get_class($result));

        $this->assertEquals('mail@ashallendesign.co.uk', $result[0]->email);
        $this->assertEquals('', $result[0]->didYouMean);
        $this->assertEquals('mail', $result[0]->user);
        $this->assertEquals('ashallendesign.co.uk', $result[0]->domain);
        $this->assertEquals(true, $result[0]->formatValid);
        $this->assertEquals(true, $result[0]->smtpCheck);
        $this->assertEquals(true, $result[0]->role);
        $this->assertEquals(false, $result[0]->disposable);
        $this->assertEquals(false, $result[0]->free);
        $this->assertEquals(0.8, $result[0]->score);
        $this->assertEquals(now(), $result[0]->validatedAt);

        $this->assertEquals('support1@ashallendesign.co.uk', $result[1]->email);
        $this->assertEquals('support@ashallendesign.co.uk', $result[1]->didYouMean);
        $this->assertEquals('support1', $result[1]->user);
        $this->assertEquals('ashallendesign.co.uk', $result[1]->domain);
        $this->assertEquals(false, $result[1]->formatValid);
        $this->assertEquals(false, $result[1]->smtpCheck);
        $this->assertEquals(false, $result[1]->role);
        $this->assertEquals(true, $result[1]->disposable);
        $this->assertEquals(true, $result[1]->free);
        $this->assertEquals(0.7, $result[1]->score);
        $this->assertEquals(now(), $result[1]->validatedAt);
    }

    /** @test */
    public function many_emails_can_be_validated_via_the_cache()
    {
        // Set a cached value that we can get.
        Cache::shouldReceive('get')
            ->once()
            ->withArgs(['mailboxlayer_result_mail@ashallendesign.co.uk'])
            ->andReturn([
                'email'        => 'mail@ashallendesign.co.uk',
                'didYouMean'   => '',
                'user'         => 'mail',
                'domain'       => 'ashallendesign.co.uk',
                'formatValid'  => true,
                'mxFound'      => true,
                'smtpCheck'    => true,
                'catchAll'     => false,
                'role'         => true,
                'disposable'   => false,
                'free'         => false,
                'score'        => 0.8,
                'validatedAt' => now()->subDays(5)->startOfDay(),
            ]);

        // Set a cached value that we can get.
        Cache::shouldReceive('get')
            ->once()
            ->withArgs(['mailboxlayer_result_support1@ashallendesign.co.uk'])
            ->andReturn([
                'email'        => 'support1@ashallendesign.co.uk',
                'didYouMean'   => 'support@ashallendesign.co.uk',
                'user'         => 'support1',
                'domain'       => 'ashallendesign.co.uk',
                'formatValid'  => false,
                'mxFound'      => false,
                'smtpCheck'    => false,
                'catchAll'     => true,
                'role'         => false,
                'disposable'   => true,
                'free'         => true,
                'score'        => 0.7,
                'validatedAt' => now()->subYear()->startOfDay(),
            ]);

        // Assert that the HTTP client is never called.
        Http::shouldReceive('get')->never();

        $mailboxLayer = new MailboxLayer(123);

        $result = $mailboxLayer->checkMany(['mail@ashallendesign.co.uk', 'support1@ashallendesign.co.uk']);
        $this->assertEquals(Collection::class, get_class($result));

        $this->assertEquals('mail@ashallendesign.co.uk', $result[0]->email);
        $this->assertEquals('', $result[0]->didYouMean);
        $this->assertEquals('mail', $result[0]->user);
        $this->assertEquals('ashallendesign.co.uk', $result[0]->domain);
        $this->assertEquals(true, $result[0]->formatValid);
        $this->assertEquals(true, $result[0]->smtpCheck);
        $this->assertEquals(true, $result[0]->role);
        $this->assertEquals(false, $result[0]->disposable);
        $this->assertEquals(false, $result[0]->free);
        $this->assertEquals(0.8, $result[0]->score);
        $this->assertEquals(now()->subDays(5)->startOfDay(), $result[0]->validatedAt);

        $this->assertEquals('support1@ashallendesign.co.uk', $result[1]->email);
        $this->assertEquals('support@ashallendesign.co.uk', $result[1]->didYouMean);
        $this->assertEquals('support1', $result[1]->user);
        $this->assertEquals('ashallendesign.co.uk', $result[1]->domain);
        $this->assertEquals(false, $result[1]->formatValid);
        $this->assertEquals(false, $result[1]->smtpCheck);
        $this->assertEquals(false, $result[1]->role);
        $this->assertEquals(true, $result[1]->disposable);
        $this->assertEquals(true, $result[1]->free);
        $this->assertEquals(0.7, $result[1]->score);
        $this->assertEquals(now()->subYear()->startOfDay(), $result[1]->validatedAt);
    }
}
