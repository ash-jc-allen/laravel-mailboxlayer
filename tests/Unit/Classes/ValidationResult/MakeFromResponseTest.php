<?php

namespace AshAllenDesign\MailboxLayer\Tests\Unit\Classes\ValidationResult;

use AshAllenDesign\MailboxLayer\Classes\ValidationResult;
use AshAllenDesign\MailboxLayer\Tests\Unit\TestCase;
use Carbon\Carbon;

class MakeFromResponseTest extends TestCase
{
    /** @test */
    public function new_object_is_returned_with_correct_fields_set_and_the_validatedAt_date_is_already_set()
    {
        Carbon::setTestNow(now());

        $responseData = [
            'email'        => 'mai1l@ashallendesign.co.uk',
            'did_you_mean' => 'mail@ashallendesign.co.uk',
            'user'         => 'mai1l',
            'domain'       => 'ashallendesign.co.uk',
            'format_valid' => true,
            'smtp_check'   => true,
            'role'         => true,
            'disposable'   => false,
            'free'         => false,
            'score'        => 0.8,
            'validated_at' => now(),
        ];

        $newObject = ValidationResult::makeFromResponse($responseData);

        $this->assertEquals('mai1l@ashallendesign.co.uk', $newObject->email);
        $this->assertEquals('mail@ashallendesign.co.uk', $newObject->didYouMean);
        $this->assertEquals('mai1l', $newObject->user);
        $this->assertEquals('ashallendesign.co.uk', $newObject->domain);
        $this->assertEquals(true, $newObject->formatValid);
        $this->assertEquals(true, $newObject->smtpCheck);
        $this->assertEquals(true, $newObject->role);
        $this->assertEquals(false, $newObject->disposable);
        $this->assertEquals(false, $newObject->free);
        $this->assertEquals(0.8, $newObject->score);
        $this->assertEquals(now(), $newObject->validatedAt);
    }

    /** @test */
    public function new_object_is_returned_with_correct_fields_set_and_the_validatedAt_date_is_not_already_set()
    {
        Carbon::setTestNow(now());

        $responseData = [
            'email'        => 'mai1l@ashallendesign.co.uk',
            'did_you_mean' => 'mail@ashallendesign.co.uk',
            'user'         => 'mai1l',
            'domain'       => 'ashallendesign.co.uk',
            'format_valid' => true,
            'smtp_check'   => true,
            'role'         => true,
            'disposable'   => false,
            'free'         => false,
            'score'        => 0.8,
        ];

        $newObject = ValidationResult::makeFromResponse($responseData);

        $this->assertEquals('mai1l@ashallendesign.co.uk', $newObject->email);
        $this->assertEquals('mail@ashallendesign.co.uk', $newObject->didYouMean);
        $this->assertEquals('mai1l', $newObject->user);
        $this->assertEquals('ashallendesign.co.uk', $newObject->domain);
        $this->assertEquals(true, $newObject->formatValid);
        $this->assertEquals(true, $newObject->smtpCheck);
        $this->assertEquals(true, $newObject->role);
        $this->assertEquals(false, $newObject->disposable);
        $this->assertEquals(false, $newObject->free);
        $this->assertEquals(0.8, $newObject->score);
        $this->assertEquals(now(), $newObject->validatedAt);
    }

}
