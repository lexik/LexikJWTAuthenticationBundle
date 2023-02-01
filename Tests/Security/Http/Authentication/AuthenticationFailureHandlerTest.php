<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Security\Http\Authentication;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationFailureHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * AuthenticationFailureHandlerTest.
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class AuthenticationFailureHandlerTest extends TestCase
{
    /**
     * test onAuthenticationFailure method.
     */
    public function testOnAuthenticationFailure()
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $authenticationException = $this->getAuthenticationException();

        $handler = new AuthenticationFailureHandler($dispatcher);
        $response = $handler->onAuthenticationFailure($this->getRequest(), $authenticationException);
        $content = json_decode($response->getContent(), true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertSame(401, $content['code']);
        $this->assertSame($authenticationException->getMessageKey(), $content['message']);
    }

    /**
     * test onAuthenticationFailure method.
     */
    public function testOnAuthenticationFailureWithANonDefaultHttpFailureStatusCode()
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $authenticationException = new AuthenticationException('', 403);

        $handler = new AuthenticationFailureHandler($dispatcher);
        $response = $handler->onAuthenticationFailure($this->getRequest(), $authenticationException);
        $content = json_decode($response->getContent(), true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame(403, $content['code']);
        $this->assertSame($authenticationException->getMessageKey(), $content['message']);
    }

    /**
     * test onAuthenticationFailure method.
     *
     * @dataProvider nonHttpStatusCodeProvider
     *
     * @param string|int $nonHttpStatusCode
     */
    public function testOnAuthenticationFailureWithANonHttpStatusCode($nonHttpStatusCode)
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $authenticationException = new AuthenticationException('', $nonHttpStatusCode);

        $handler = new AuthenticationFailureHandler($dispatcher);
        $response = $handler->onAuthenticationFailure($this->getRequest(), $authenticationException);
        $content = json_decode($response->getContent(), true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertSame(401, $content['code']);
        $this->assertSame($authenticationException->getMessageKey(), $content['message']);
    }

    public function testOnAuthenticationFailureWithTranslator()
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->once())
            ->method('trans')->with('An authentication exception occurred.', [])
            ->willReturn('translated message');

        $authenticationException = new AuthenticationException('message to translate');

        $handler = new AuthenticationFailureHandler($dispatcher, $translator);
        $response = $handler->onAuthenticationFailure($this->getRequest(), $authenticationException);
        $content = json_decode($response->getContent(), true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertSame(401, $content['code']);
        $this->assertSame('translated message', $content['message']);
    }

    public static function nonHttpStatusCodeProvider(): iterable
    {
        yield 'server error HTTP status code' => [500];
        yield 'redirection HTTP status code' => [500];
        yield 'success HTTP status code' => [500];
        yield 'non HTTP status code' => [1302];
        yield 'default status code' => [0];
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject&Request
     */
    protected function getRequest()
    {
        return $this->createMock(Request::class);
    }

    protected function getAuthenticationException(): AuthenticationException
    {
        return new AuthenticationException();
    }
}
