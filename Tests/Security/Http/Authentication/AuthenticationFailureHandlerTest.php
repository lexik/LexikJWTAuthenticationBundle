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
        $dispatcher = $this
            ->getMockBuilder(EventDispatcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $authenticationException = $this->getAuthenticationException();

        $handler = new AuthenticationFailureHandler($dispatcher);
        $response = $handler->onAuthenticationFailure($this->getRequest(), $authenticationException);
        $content = json_decode($response->getContent(), true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertEquals(401, $content['code']);
        $this->assertEquals($authenticationException->getMessageKey(), $content['message']);
    }

    /**
     * test onAuthenticationFailure method.
     */
    public function testOnAuthenticationFailureWithANonDefaultHttpFailureStatusCode()
    {
        $dispatcher = $this
            ->getMockBuilder(EventDispatcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $authenticationException = new AuthenticationException('', 403);

        $handler = new AuthenticationFailureHandler($dispatcher);
        $response = $handler->onAuthenticationFailure($this->getRequest(), $authenticationException);
        $content = json_decode($response->getContent(), true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertEquals(403, $content['code']);
        $this->assertEquals($authenticationException->getMessageKey(), $content['message']);
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
        $dispatcher = $this
            ->getMockBuilder(EventDispatcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $authenticationException = new AuthenticationException('', $nonHttpStatusCode);

        $handler = new AuthenticationFailureHandler($dispatcher);
        $response = $handler->onAuthenticationFailure($this->getRequest(), $authenticationException);
        $content = json_decode($response->getContent(), true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertEquals(401, $content['code']);
        $this->assertEquals($authenticationException->getMessageKey(), $content['message']);
    }

    public function testOnAuthenticationFailureWithTranslator()
    {
        $dispatcher = $this
            ->getMockBuilder(EventDispatcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $translator = $this
            ->getMockBuilder(TranslatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translator->expects($this->once())
            ->method('trans')->with('An authentication exception occurred.', [])
            ->willReturn('translated message');

        $authenticationException = new AuthenticationException('message to translate');

        $handler = new AuthenticationFailureHandler($dispatcher, $translator);
        $response = $handler->onAuthenticationFailure($this->getRequest(), $authenticationException);
        $content = json_decode($response->getContent(), true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertEquals(401, $content['code']);
        $this->assertEquals('translated message', $content['message']);
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRequest()
    {
        return $this
            ->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return AuthenticationException
     */
    protected function getAuthenticationException()
    {
        return new AuthenticationException();
    }
}
