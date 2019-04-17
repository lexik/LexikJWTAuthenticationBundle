<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Security\Http\Authentication;

use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractsEventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * AuthenticationSuccessHandlerTest.
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class AuthenticationSuccessHandlerTest extends TestCase
{
    /**
     * test onAuthenticationSuccess method.
     */
    public function testOnAuthenticationSuccess()
    {
        $request = $this->getRequest();
        $token   = $this->getToken();

        $response = (new AuthenticationSuccessHandler($this->getJWTManager('secrettoken'), $this->getDispatcher()))
            ->onAuthenticationSuccess($request, $token);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $content);
        $this->assertEquals('secrettoken', $content['token']);
    }

    public function testHandleAuthenticationSuccess()
    {
        $response = (new AuthenticationSuccessHandler($this->getJWTManager('secrettoken'), $this->getDispatcher()))
            ->handleAuthenticationSuccess($this->getUser());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $content);
        $this->assertEquals('secrettoken', $content['token']);
    }

    public function testHandleAuthenticationSuccessWithGivenJWT()
    {
        $response = (new AuthenticationSuccessHandler($this->getJWTManager(), $this->getDispatcher()))
            ->handleAuthenticationSuccess($this->getUser(), 'jwt');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $content);
        $this->assertEquals('jwt', $content['token']);
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
        $token = $this
            ->getMockBuilder('Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken')
            ->disableOriginalConstructor()
            ->getMock();

        $token
            ->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($this->getUser()));

        return $token;
    }

    private function getUser()
    {
        $user = $this
            ->getMockBuilder('Symfony\Component\Security\Core\User\UserInterface')
            ->getMock();

        $user
            ->expects($this->any())
            ->method('getUsername')
            ->will($this->returnValue('username'));

        return $user;
    }

    private function getJWTManager($token = null)
    {
        $jwtManager = $this->getMockBuilder(JWTManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        if (null !== $token) {
            $jwtManager
                ->expects($this->any())
                ->method('create')
                ->will($this->returnValue('secrettoken'));
        }

        return $jwtManager;
    }

    private function getDispatcher()
    {
        $dispatcher = $this->getMockBuilder(EventDispatcher::class)
            ->disableOriginalConstructor()
            ->getMock();

        if ($dispatcher instanceof ContractsEventDispatcherInterface) {
            $dispatcher
                ->expects($this->once())
                ->method('dispatch')
                ->with(
                    $this->isInstanceOf('Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent'),
                    $this->equalTo(Events::AUTHENTICATION_SUCCESS)
                );
        } else {
            $dispatcher
                ->expects($this->once())
                ->method('dispatch')
                ->with(
                    $this->equalTo(Events::AUTHENTICATION_SUCCESS),
                    $this->isInstanceOf('Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent')
                );
        }

        return $dispatcher;
    }
}
