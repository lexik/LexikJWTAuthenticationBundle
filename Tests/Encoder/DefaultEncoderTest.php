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
     * Tests calling DefaultEncoder::decode() with an invalid token.
     */
    public function testDecodeValidSignature()
    {
        $payload   = ['username' => 'chalasr'];
        $loadedJWS = $this->getLoadedJWSMock();
        $loadedJWS
            ->expects($this->once())
            ->method('getPayload')
            ->willReturn($payload);

        $loadedJWS
            ->expects($this->once())
            ->method('isVerified')
            ->willReturn(true); // Mark the signature as verified

        $jwsProvider = $this->getJWSProviderMock();
        $jwsProvider
            ->expects($this->once())
            ->method('load')
            ->willReturn($loadedJWS);

        $encoder = new DefaultEncoder($jwsProvider);

        $this->assertSame($payload, $encoder->decode('jwt'));
    }

    /**
     * Tests the failure on calling DefaultEncoder::encode() with an unsigned JWS.
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
     * Tests that the failure on calling DefaultEncoder::decode() with an invalid token.
     *
     * @expectedException Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailure\JWTDecodeFailureException
     */
    public function testDecodeInvalidToken()
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getJWSProviderMock()
    {
        return $this->getMockBuilder(JWSProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getLoadedJWSMock()
    {
        return $this->getMockBuilder(LoadedJWS::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
