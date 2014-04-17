<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Security\Http\Authentication;

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

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $content);
        $this->assertEquals('tokenstring', $content['token']);
    }

    /**
     * @return AuthenticationSuccessHandler
     */
    protected function getHandler()
    {
        $jws = $this
            ->getMockBuilder('Namshi\JOSE\JWS')
            ->disableOriginalConstructor()
            ->getMock();

        $jws
            ->expects($this->any())
            ->method('getTokenString')
            ->will($this->returnValue('tokenstring'));

        $encoder = $this->getMockBuilder('Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoder')
            ->disableOriginalConstructor()
            ->getMock();

        $encoder
            ->expects($this->any())
            ->method('encode')
            ->will($this->returnValue($jws));

        $dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')
            ->disableOriginalConstructor()
            ->getMock();

        return new AuthenticationSuccessHandler($encoder, $dispatcher, 3600);
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
