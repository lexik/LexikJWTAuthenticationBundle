<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional;

/**
 * Tests the built-in authentication response mechanism.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class DefaultTokenAuthenticationTest extends CompleteTokenAuthenticationTest
{
    public function testAccessSecuredRouteWithoutToken()
    {
        $response = parent::testAccessSecuredRouteWithoutToken();

        $this->assertEquals('JWT Token not found', $response['message']);
    }

    public function testAccessSecuredRouteWithInvalidToken($token = 'dummy')
    {
        $response = parent::testAccessSecuredRouteWithInvalidToken($token);

        $this->assertEquals('Invalid JWT Token', $response['message']);
    }

    /**
     * @group time-sensitive
     */
    public function testAccessSecuredRouteWithExpiredToken($fail = true)
    {
        $response = parent::testAccessSecuredRouteWithExpiredToken();

        $this->assertSame('Expired JWT Token', $response['message']);
    }
}
