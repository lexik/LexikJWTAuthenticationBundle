<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Signature;

use Lexik\Bundle\JWTAuthenticationBundle\Signature\LoadedJWS;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * Tests the CreatedJWS model class.
 *
 * @group time-sensitive
 */
final class LoadedJWSTest extends TestCase
{
    private ?array $goodPayload = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->goodPayload = [
            'username' => 'chalasr',
            'exp' => time() + 3600,
            'iat' => time(),
        ];

        parent::setUp();
    }

    public function testVerifiedWithEmptyPayload()
    {
        $jws = new LoadedJWS($payload = [], true);

        $this->assertSame($payload, $jws->getPayload());
        $this->assertFalse($jws->isVerified());
        $this->assertFalse($jws->isExpired());
    }

    public function testUnverifiedWithGoodPayload()
    {
        $jws = new LoadedJWS($this->goodPayload, false);

        $this->assertSame($this->goodPayload, $jws->getPayload());
        $this->assertFalse($jws->isExpired());
        $this->assertFalse($jws->isVerified());
    }

    public function testVerifiedWithGoodPayload()
    {
        $jws = new LoadedJWS($this->goodPayload, true);

        $this->assertSame($this->goodPayload, $jws->getPayload());
        $this->assertFalse($jws->isExpired());
        $this->assertTrue($jws->isVerified());
    }

    public function testVerifiedWithExpiredPayload()
    {
        $payload = $this->goodPayload;
        $payload['exp'] -= 3600;

        $jws = new LoadedJWS($payload, true);

        $this->assertFalse($jws->isVerified());
        $this->assertTrue($jws->isExpired());
    }

    public function testAllowNoExpWithGoodPayload()
    {
        $payload = $this->goodPayload;
        unset($payload['exp']);

        $jws = new LoadedJWS($payload, true, false);

        $this->assertTrue($jws->isVerified());
    }

    public function testNoExpWithGoodPayload()
    {
        $payload = $this->goodPayload;
        unset($payload['exp']);

        $jws = new LoadedJWS($payload, true, true);

        $this->assertTrue($jws->isInvalid());
        $this->assertFalse($jws->isVerified());
    }

    public function testVerifiedWithExpiredPayloadAccountedForByClockSkew()
    {
        $payload = $this->goodPayload;
        $payload['exp'] -= 3600;

        $jws = new LoadedJWS($payload, true, true, [], 60);

        $this->assertTrue($jws->isVerified());
        $this->assertFalse($jws->isExpired());
    }

    public function testIsInvalidReturnsTrueWithIssuedAtSetInTheFuture()
    {
        $payload = $this->goodPayload;
        $payload['iat'] += 3600;

        $jws = new LoadedJWS($payload, true);

        $this->assertFalse($jws->isVerified());
        $this->assertFalse($jws->isExpired());
        $this->assertTrue($jws->isInvalid());
    }

    public function testIsInvalidReturnsFalseWithIssuedAtSetInTheFutureButAccountedForByClockSkew()
    {
        $payload = $this->goodPayload;
        $payload['iat'] += 3600;

        $jws = new LoadedJWS($payload, true, true, [], 3660);

        $this->assertTrue($jws->isVerified());
        $this->assertFalse($jws->isExpired());
        $this->assertFalse($jws->isInvalid());
    }

    public function testIsNotExpiredDaySavingTransition()
    {
        // 2020-10-25 00:16:13 UTC+0
        $timestamp = 1_603_584_973;
        ClockMock::withClockMock($timestamp);

        $dstPayload = [
            'username' => 'test',
            'exp' => $timestamp + 3600,
            'iat' => $timestamp,
        ];

        $jws = new LoadedJWS($dstPayload, true);

        $this->assertFalse($jws->isExpired());
        $this->assertTrue($jws->isVerified());
    }
}
