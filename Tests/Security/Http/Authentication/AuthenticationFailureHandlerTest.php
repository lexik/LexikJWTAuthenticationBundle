<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Security\Http\Authentication;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationFailureHandler;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * AuthenticationFailureHandlerTest.
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class AuthenticationFailureHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test onAuthenticationFailure method.
     */
    public function testOnAuthenticationFailure()
    {
        $dispatcher = $this
            ->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $handler  = new AuthenticationFailureHandler($dispatcher);
        $response = $handler->onAuthenticationFailure($this->getRequest(), $this->getAuthenticationException());
        $content  = json_decode($response->getContent(), true);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals(401, $content['code']);
        $this->assertEquals('Bad credentials', $content['message']);
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
        return new AuthenticationException();
    }
}
