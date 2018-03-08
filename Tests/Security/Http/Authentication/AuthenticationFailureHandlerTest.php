<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Security\Http\Authentication;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationFailureHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

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
            ->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $handler  = new AuthenticationFailureHandler($dispatcher);
        $response = $handler->onAuthenticationFailure($this->getRequest(), $this->getAuthenticationException());
        $responseCustom = $handler->onAuthenticationFailure($this->getRequest(), $this->getAuthenticationException('User account is disabled.'));
        $content  = json_decode($response->getContent(), true);
        $contentCustom  = json_decode($responseCustom->getContent(), true);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals(401, $content['code']);
        $this->assertEquals('Bad credentials', $content['message']);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $responseCustom);
        $this->assertEquals(401, $responseCustom->getStatusCode());
        $this->assertEquals(401, $contentCustom['code']);
        $this->assertEquals('User account is disabled.', $contentCustom['message']);
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
     * @param string $exceptionMessage
     *
     * @return AuthenticationException
     */
    protected function getAuthenticationException($exceptionMessage = '')
    {
        if ('' != $exceptionMessage) {
            return new AuthenticationException($exceptionMessage);
        }

        return new AuthenticationException();
    }
}
