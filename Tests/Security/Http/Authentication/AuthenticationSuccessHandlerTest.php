<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Security\Http\Authentication;

use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;

/**
 * AuthenticationSuccessHandlerTest
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class AuthenticationSuccessHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test onAuthenticationSuccess method
     */
    public function testOnAuthenticationSuccess()
    {
        $request = $this->getRequest();
        $token = $this->getToken();

        $response = $this->getHandler()->onAuthenticationSuccess($request, $token);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
        $this->assertEquals(200, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $content);
        $this->assertEquals('secrettoken', $content['token']);
    }

    /**
     * @return AuthenticationSuccessHandler
     */
    protected function getHandler()
    {
        $jwtManager = $this->getMockBuilder('Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager')
            ->disableOriginalConstructor()
            ->getMock();

        $jwtManager
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue('secrettoken'));

        $dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')
            ->disableOriginalConstructor()
            ->getMock();

        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo(Events::AUTHENTICATION_SUCCESS),
                $this->isInstanceOf('Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent')
            );

        return new AuthenticationSuccessHandler($jwtManager, $dispatcher);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRequest()
    {
        $request = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        return $request;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getToken()
    {
        $user = $this
            ->getMockBuilder('Symfony\Component\Security\Core\User\UserInterface')
            ->getMock();

        $user
            ->expects($this->any())
            ->method('getUsername')
            ->will($this->returnValue('username'));

        $token = $this
            ->getMockBuilder('Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken')
            ->disableOriginalConstructor()
            ->getMock();

        $token
            ->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user));

        return $token;
    }
}
