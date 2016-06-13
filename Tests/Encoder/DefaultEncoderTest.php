<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Encoder;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\DefaultEncoder;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\CreatedJWS;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\LoadedJWS;

/**
 * Tests the DefaultEncoder.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class DefaultEncoderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests calling DefaultEncoder::decode() with a valid signature and payload.
     */
    public function testDecodeFromValidJWS()
    {
        $payload     = [
            'username' => 'chalasr',
            'exp'      => (new \DateTime('now'))->format('U') + 86400,
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
     * @expectedException Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailure\UnsignedJWTEncodeFailureException
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
     * @expectedException Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailure\JWTDecodeFailureException
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
     * @expectedException Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailure\ExpiredJWTDecodeFailureException
     */
    public function testDecodeFromExpiredPayload()
    {
        $loadedJWS   = new LoadedJWS(['exp' => (new \DateTime('now'))->format('U') - 86400], true);
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
        return $this->getMockBuilder(JWSProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
