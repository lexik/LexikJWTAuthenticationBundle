<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Encoder;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\DefaultEncoder;

/**
 * Tests the DefaultEncoder.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class DefaultEncoderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests calling DefaultEncoder::decode() with an invalid token.
     *
     * @expectedException Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailure\JWTDecodeFailureException
     */
    public function testDecodeInvalidToken()
    {
        $encoder = new DefaultEncoder($this->getJWSProviderMock());
        $encoder->decode('secrettoken');
    }

    /**
     * Tests calling DefaultEncoder::encode() with an invalid configuration.
     *
     * @expectedException Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailure\UnsignedJWTEncodeFailureException
     */
    public function testEncode()
    {
        $encoder = new DefaultEncoder($this->getJWSProviderMock());
        $encoder->encode([]);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getJWSProviderMock()
    {
        return $this->getMockBuilder('Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
