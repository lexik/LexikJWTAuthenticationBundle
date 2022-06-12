<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Security\Authentication\Firewall;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Firewall\JWTListener;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * JWTListenerTest.
 *
 * @group legacy
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class JWTListenerTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!interface_exists(AuthenticationManagerInterface::class)) {
            self::markTestSkipped('Test only applies to symfony/security-core 5.4 and earlier');
        }
    }

    /**
     * @group time-sensitive
     */
    public function testHandle()
    {
        $listener = new JWTListener($this->getTokenStorageMock(), $this->getAuthenticationManagerMock());
        $listener->setDispatcher($this->getEventDispatcherMock());
        $this->assertNull($listener($this->getEvent()));

        // one token extractor with no result : should return void

        $listener = new JWTListener($this->getTokenStorageMock(), $this->getAuthenticationManagerMock());
        $dispatcher = $this->getEventDispatcherMock();
        $this->expectEvent(Events::JWT_NOT_FOUND, JWTNotFoundEvent::class, $dispatcher);

        $listener->setDispatcher($dispatcher);
        $listener->addTokenExtractor($this->getAuthorizationHeaderTokenExtractorMock(false));
        $this->assertNull($listener($this->getEvent()));

        // request token found : should enter authentication process

        $authenticationManager = $this->getAuthenticationManagerMock();
        $authenticationManager->expects($this->once())->method('authenticate');

        $listener = new JWTListener($this->getTokenStorageMock(), $authenticationManager);
        $listener->setDispatcher($this->getEventDispatcherMock());
        $listener->addTokenExtractor($this->getAuthorizationHeaderTokenExtractorMock('token'));
        $listener($this->getEvent());

        // request token found : authentication fail

        $invalidTokenException = new AuthenticationException('Invalid JWT Token');
        $authenticationManager = $this->getAuthenticationManagerMock();
        $authenticationManager
            ->expects($this->once())
            ->method('authenticate');
        $authenticationManager
            ->expects($this->once())
            ->method('authenticate')
            ->will($this->throwException($invalidTokenException));

        $listener = new JWTListener($this->getTokenStorageMock(), $authenticationManager);
        $dispatcher = $this->getEventDispatcherMock();
        $this->expectEvent(Events::JWT_INVALID, JWTInvalidEvent::class, $dispatcher);

        $listener->setDispatcher($dispatcher);
        $listener->addTokenExtractor($this->getAuthorizationHeaderTokenExtractorMock('token'));

        $event = $this->getEvent();
        $event
            ->expects($this->once())
            ->method('setResponse')
            ->with(new JWTAuthenticationFailureResponse($invalidTokenException->getMessage()));

        $listener($event);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getAuthenticationManagerMock()
    {
        return $this
            ->getMockBuilder(AuthenticationManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getTokenStorageMock()
    {
        return $this
            ->getMockBuilder(TokenStorageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param mixed $returnValue
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getAuthorizationHeaderTokenExtractorMock($returnValue)
    {
        $extractor = $this
            ->getMockBuilder(AuthorizationHeaderTokenExtractor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $extractor
            ->expects($this->any())
            ->method('extract')
            ->will($this->returnValue($returnValue));

        return $extractor;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEvent()
    {
        $request = $this
            ->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event = $this
            ->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        return $event;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEventDispatcherMock()
    {
        return $this->getMockBuilder(EventDispatcher::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function expectEvent($eventName, $eventType, $dispatcher)
    {
        $dispatcher->expects($this->once())->method('dispatch')->with($this->isInstanceOf($eventType), $eventName);
    }
}
