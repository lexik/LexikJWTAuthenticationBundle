<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Signature;

use Lexik\Bundle\JWTAuthenticationBundle\Signature\LoadedJWS;
use Lexik\Bundle\JWTAuthenticationBundle\Tests\ForwardCompatTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * Tests the CreatedJWS model class.
 *
 * @group time-sensitive
 */
final class LoadedJWSTest extends TestCase
{
    use ForwardCompatTestCaseTrait;

    private $goodPayload;

    /**
     * {@inheritdoc}
     */
    protected function doSetUp()
    {
        $this->goodPayload = [
            'username' => 'chalasr',
            'exp' => time() + 3600,
            'iat' => time(),
        ];
    }

    public function testVerifiedWithEmptyPayload()
    {
        $jws = new LoadedJWS($payload = [], true);

        $this->assertSame($payload, $jws->getPayload());
        $this->assertTrue($jws->isInvalid());
    }

    public function testUnverifiedWithGoodPayload()
    {
        $jws = new LoadedJWS($this->goodPayload, false);

        $this->assertSame($this->goodPayload, $jws->getPayload());
        $this->assertFalse($jws->isVerified());
    }

    public function testVerifiedWithGoodPayload()
    {
        $jws = new LoadedJWS($this->goodPayload, true);

        $this->assertSame($this->goodPayload, $jws->getPayload());
        $this->assertTrue($jws->isVerified());
    }

    public function testVerifiedWithExpiredPayload()
    {
        $payload = $this->goodPayload;
        $payload['exp'] -= 3600;

        $jws = new LoadedJWS($payload, true);

        $this->assertTrue($jws->isExpired());
        $this->assertFalse($jws->isVerified());
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

        $jws = new LoadedJWS($payload, true, false, [], 60);

        $this->assertTrue($jws->isVerified());
    }

    public function testIsInvalidReturnsTrueWithIssuedAtSetInTheFuture()
    {
        $payload = $this->goodPayload;
        $payload['iat'] += 3600;

        $jws = new LoadedJWS($payload, true);

        $this->assertTrue($jws->isInvalid());
        $this->assertFalse($jws->isVerified());
    }

    public function testIsInvalidReturnsFalseWithIssuedAtSetInTheFutureButAccountedForByClockSkew()
    {
        $payload = $this->goodPayload;
        $payload['iat'] += 3600;

        $jws = new LoadedJWS($payload, true, false, [], 3660);

        $this->assertTrue($jws->isVerified());
    }

    public function testIsNotExpiredDaySavingTransition()
    {
        // 2020-10-25 00:16:13 UTC+0
        $timestamp = 1603584973;
        ClockMock::withClockMock($timestamp);

        $dstPayload = [
            'username' => 'test',
            'exp' => $timestamp + 3600,
            'iat' => $timestamp,
        ];

        $jws = new LoadedJWS($dstPayload, true);

        $this->assertTrue($jws->isVerified());
    }
}
