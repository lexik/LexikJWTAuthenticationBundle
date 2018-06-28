<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Encoder;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\DefaultEncoder;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\DefaultJWSProvider;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\CreatedJWS;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\LoadedJWS;
use PHPUnit\Framework\TestCase;

/**
 * Tests the DefaultEncoder.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 *
 * @group legacy
 */
class DefaultEncoderTest extends TestCase
{
    /**
     * Tests calling DefaultEncoder::decode() with a valid signature and payload.
     */
    public function testDecodeFromValidJWS()
    {
        $payload = [
            'username' => 'chalasr',
            'exp'      => time() + 3600,
        ];

        $loadedJWS   = new LoadedJWS($payload, true);
        $jwsProvider = $this->getJWSProviderMock();
        $jwsProvider
            ->expects($this->once())
            ->method('load')
            ->willReturn($loadedJWS);

        $encoder = new DefaultEncoder($jwsProvider);

        $this->assertSame($payload, $encoder->decode('jwt'));
    }

    /**
     * Tests calling DefaultEncoder::encode() with a signed token.
     */
    public function testEncodeFromValidJWS()
    {
        $createdJWS  = new CreatedJWS('jwt', true);
        $jwsProvider = $this->getJWSProviderMock();
        $jwsProvider
            ->expects($this->once())
            ->method('create')
            ->willReturn($createdJWS);

        $encoder = new DefaultEncoder($jwsProvider);

        $this->assertSame('jwt', $encoder->encode([]));
    }

    /**
     * Tests that calling DefaultEncoder::encode() with an unsigned JWS correctly fails.
     *
     * @expectedException \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException
     */
    public function testEncodeFromUnsignedJWS()
    {
        $jwsProvider = $this->getJWSProviderMock();
        $jwsProvider
            ->expects($this->once())
            ->method('create')
            ->willReturn(new CreatedJWS('jwt', false));

        $encoder = new DefaultEncoder($jwsProvider);
        $encoder->encode([]);
    }

    /**
     * Tests that calling DefaultEncoder::decode() with an unverified signature correctly fails.
     *
     * @expectedException \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException
     */
    public function testDecodeFromUnverifiedJWS()
    {
        $jwsProvider = $this->getJWSProviderMock();
        $jwsProvider
            ->expects($this->once())
            ->method('load')
            ->willReturn(new LoadedJWS([], false));

        $encoder = new DefaultEncoder($jwsProvider);
        $encoder->decode('secrettoken');
    }

    /**
     * Tests that calling DefaultEncoder::decode() with an expired payload correctly fails.
     *
     * @expectedException        \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException
     * @expectedExceptionMessage Expired JWT Token
     */
    public function testDecodeFromExpiredPayload()
    {
        $loadedJWS   = new LoadedJWS(['exp' => time() - 3600], true);
        $jwsProvider = $this->getJWSProviderMock();
        $jwsProvider
            ->expects($this->once())
            ->method('load')
            ->willReturn($loadedJWS);

        $encoder = new DefaultEncoder($jwsProvider);
        $encoder->decode('jwt');
    }

    /**
     * Tests that calling DefaultEncoder::decode() with an iat set in the future correctly fails.
     *
     * @expectedException        \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException
     * @expectedExceptionMessage Invalid JWT Token
     */
    public function testDecodeWithInvalidIssudAtClaimInPayload()
    {
        $loadedJWS   = new LoadedJWS(['exp' => time() + 3600, 'iat' => time() + 3600], true);
        $jwsProvider = $this->getJWSProviderMock();
        $jwsProvider
            ->expects($this->once())
            ->method('load')
            ->willReturn($loadedJWS);

        $encoder = new DefaultEncoder($jwsProvider);
        $encoder->decode('jwt');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getJWSProviderMock()
    {
        return $this->getMockBuilder(DefaultJWSProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
