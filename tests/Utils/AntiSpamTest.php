<?php

namespace App\Tests\Utils;

use App\Utils\AntiSpam;
use PHPUnit\Framework\TestCase;

final class AntiSpamTest extends TestCase
{
    private AntiSpam $antiSpam;

    protected function setUp(): void
    {
        $this->antiSpam = new AntiSpam('secret', 3);
    }

    public function testTokenOlderThanMinDelayIsValid()
    {
        $token = $this->antiSpam->generateToken(time() - 3);
        $this->assertTrue($this->antiSpam->isTokenValid($token));
    }

    public function testFreshTokenIsRejected()
    {
        $token = $this->antiSpam->generateToken();
        $this->assertFalse($this->antiSpam->isTokenValid($token));
    }

    public function testFreshTokenIsValidWithoutMinDelay()
    {
        $antiSpam = new AntiSpam('secret', 0);
        $this->assertTrue($antiSpam->isTokenValid($antiSpam->generateToken()));
    }

    public function testTamperedTimestampIsRejected()
    {
        $token = $this->antiSpam->generateToken(time() - 3);
        [, $signature] = explode('.', $token);
        $this->assertFalse($this->antiSpam->isTokenValid((time() - 3600).'.'.$signature));
    }

    public function testTokenSignedWithAnotherSecretIsRejected()
    {
        $token = (new AntiSpam('another-secret', 3))->generateToken(time() - 3);
        $this->assertFalse($this->antiSpam->isTokenValid($token));
    }

    public function testFutureTokenIsRejected()
    {
        $antiSpam = new AntiSpam('secret', 0);
        $this->assertFalse($antiSpam->isTokenValid($antiSpam->generateToken(time() + 3600)));
    }

    /**
     * @dataProvider malformedTokenProvider
     */
    public function testMalformedTokenIsRejected(?string $token)
    {
        $this->assertFalse($this->antiSpam->isTokenValid($token));
    }

    public static function malformedTokenProvider(): array
    {
        return [
            'null' => [null],
            'empty' => [''],
            'no signature' => ['1755000000'],
            'not a timestamp' => ['abc.def'],
            'garbage' => ['spam'],
        ];
    }
}
