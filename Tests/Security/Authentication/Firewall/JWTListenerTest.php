<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Security\Authentication\Firewall;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Firewall\JWTListener;

/**
 * JWTListenerTest
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class JWTListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test handle method
     */
    public function testHandle()
    {
        // no token extractor : should return void

        $listener = new JWTListener($this->getTokenStorageMock(), $this->getAuthenticationManagerMock());
        $this->assertNull($listener->handle($this->getEvent()));

        // one token extractor with no result : should return void

        $listener = new JWTListener($this->getTokenStorageMock(), $this->getAuthenticationManagerMock());
        $listener->addTokenExtractor($this->getAuthorizationHeaderTokenExtractorMock(false));
        $this->assertNull($listener->handle($this->getEvent()));

        // request token found : should enter authentication process

        $authenticationManager = $this->getAuthenticationManagerMock();
        $authenticationManager->expects($this->once())->method('authenticate');

        $listener = new JWTListener($this->getTokenStorageMock(), $authenticationManager);
        $listener->addTokenExtractor($this->getAuthorizationHeaderTokenExtractorMock('token'));
        $listener->handle($this->getEvent());

        // request token found : authentication fail

        $authenticationManager = $this->getAuthenticationManagerMock();
        $authenticationManager
            ->expects($this->once())
            ->method('authenticate');
        $authenticationManager
            ->expects($this->once())
            ->method('authenticate')
            ->will($this->throwException(new \Symfony\Component\Security\Core\Exception\AuthenticationException()));

        $listener = new JWTListener($this->getTokenStorageMock(), $authenticationManager);
        $listener->addTokenExtractor($this->getAuthorizationHeaderTokenExtractorMock('token'));

        $event = $this->getEvent();
        $event->expects($this->once())->method('setResponse');

        $listener->handle($event);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getAuthenticationManagerMock()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getTokenStorageMock()
    {
        if (interface_exists('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')) {
            $class = 'Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface';
        } else {
            $class = 'Symfony\Component\Security\Core\SecurityContext';
        }

        return $this
            ->getMockBuilder($class)
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
            ->getMockBuilder('Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor')
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
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $event = $this
            ->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        return $event;
    }
}
