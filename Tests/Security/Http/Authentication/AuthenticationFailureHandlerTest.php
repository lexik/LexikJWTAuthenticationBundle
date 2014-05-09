<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Security\Http\Authentication;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationFailureHandler;

/**
 * AuthenticationFailureHandlerTest
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class AuthenticationFailureHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test onAuthenticationFailure method
     */
    public function testOnAuthenticationFailure()
    {
        $handler = new AuthenticationFailureHandler();
        $response = $handler->onAuthenticationFailure($this->getRequest(), $this->getAuthenticationException());

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRequest()
    {
        return $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getAuthenticationException()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Security\Core\Exception\AuthenticationException')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
