<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Security\Http\Authentication;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\Token\JWTPostAuthenticationToken;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Cookie\JWTCookieProvider;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\UserInterface;

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
        $token = $this->getToken();

        $response = (new AuthenticationSuccessHandler($this->getJWTManager('secrettoken'), $this->getDispatcher()))
            ->onAuthenticationSuccess($request, $token);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), $response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $content);
        $this->assertSame('secrettoken', $content['token']);
    }

    public function testHandleAuthenticationSuccess()
    {
        $response = (new AuthenticationSuccessHandler($this->getJWTManager('secrettoken'), $this->getDispatcher()))
            ->handleAuthenticationSuccess($this->getUser());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), $response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $content);
        $this->assertSame('secrettoken', $content['token']);
    }

    public function testHandleAuthenticationSuccessWithGivenJWT()
    {
        $response = (new AuthenticationSuccessHandler($this->getJWTManager(), $this->getDispatcher()))
            ->handleAuthenticationSuccess($this->getUser(), 'jwt');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), $response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $content);
        $this->assertSame('jwt', $content['token']);
    }

    public function testOnAuthenticationSuccessSetCookie()
    {
        $request = $this->getRequest();
        $token = $this->getToken();

        $cookieProvider = new JWTCookieProvider('access_token', 60);

        $response = (new AuthenticationSuccessHandler($this->getJWTManager('testheader.testpayload.testsignature'), $this->getDispatcher(), [$cookieProvider]))
            ->onAuthenticationSuccess($request, $token);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode(), $response->getContent());
        $this->assertEmpty(json_decode($response->getContent(), true));

        $cookie = $response->headers->getCookies()[0];
        $this->assertSame('access_token', $cookie->getName());
        $this->assertSame('testheader.testpayload.testsignature', $cookie->getValue());
    }

    public function testOnAuthenticationSuccessSetSplitCookie()
    {
        $request = $this->getRequest();
        $token = $this->getToken();

        $headerPayloadCookieProvider = new JWTCookieProvider('jwt_hp', 60, null, null, null, true, false, ['header', 'payload']);
        $signatureCookieProvider = new JWTCookieProvider('jwt_s', 60, null, null, null, true, true, ['signature']);

        $response = (new AuthenticationSuccessHandler($this->getJWTManager('secretheader.secretpayload.secretsignature'), $this->getDispatcher(), [$headerPayloadCookieProvider, $signatureCookieProvider]))
            ->onAuthenticationSuccess($request, $token);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode(), $response->getContent());
        $this->assertEmpty(json_decode($response->getContent(), true));

        $headerPayloadCookie = $response->headers->getCookies()[0];
        $this->assertSame('jwt_hp', $headerPayloadCookie->getName());
        $this->assertSame('secretheader.secretpayload', $headerPayloadCookie->getValue());

        $signatureCookie = $response->headers->getCookies()[1];
        $this->assertSame('jwt_s', $signatureCookie->getName());
        $this->assertSame('secretsignature', $signatureCookie->getValue());
    }

    /**
     * @return MockObject&Request
     */
    protected function getRequest()
    {
        return $this->createMock(Request::class);
    }

    /**
     * @return MockObject&JWTPostAuthenticationToken
     */
    protected function getToken()
    {
        $token = $this->createMock(JWTPostAuthenticationToken::class);

        $token
            ->method('getUser')
            ->willReturn($this->getUser());

        return $token;
    }

    private function getUser(): UserInterface
    {
        return new InMemoryUser('username', 'password');
    }

    /**
     * @return MockObject&JWTManager
     */
    private function getJWTManager(?string $token = null)
    {
        $jwtManager = $this->createMock(JWTManager::class);

        if (null !== $token) {
            $jwtManager
                ->method('create')
                ->willReturn($token);
        }

        return $jwtManager;
    }

    /**
     * @return MockObject&EventDispatcher
     */
    private function getDispatcher()
    {
        $dispatcher = $this->createMock(EventDispatcher::class);

        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(AuthenticationSuccessEvent::class),
                $this->equalTo(Events::AUTHENTICATION_SUCCESS)
            );

        return $dispatcher;
    }
}
