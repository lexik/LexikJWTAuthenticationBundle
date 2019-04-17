<?php

namespace Services;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\Tests\Stubs\User as CustomUser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractsEventDispatcherInterface;
use Symfony\Component\Security\Core\User\User;

/**
 * JWTManagerTest.
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class JWTManagerTest extends TestCase
{
    /**
     * test create.
     */
    public function testCreate()
    {
        $dispatcher = $this->getEventDispatcherMock();

        if ($dispatcher instanceof ContractsEventDispatcherInterface) {
            $dispatcher
                ->expects($this->at(0))
                ->method('dispatch')
                ->with(
                    $this->isInstanceOf('Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent'),
                    $this->equalTo(Events::JWT_CREATED)
                );

            $dispatcher
                ->expects($this->at(1))
                ->method('dispatch')
                ->with(
                    $this->isInstanceOf('Lexik\Bundle\JWTAuthenticationBundle\Event\JWTEncodedEvent'),
                    $this->equalTo(Events::JWT_ENCODED)
                );
        } else {
            $dispatcher
                ->expects($this->at(0))
                ->method('dispatch')
                ->with(
                    $this->equalTo(Events::JWT_CREATED),
                    $this->isInstanceOf('Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent')
                );
            $dispatcher
                ->expects($this->at(1))
                ->method('dispatch')
                ->with(
                    $this->equalTo(Events::JWT_ENCODED),
                    $this->isInstanceOf('Lexik\Bundle\JWTAuthenticationBundle\Event\JWTEncodedEvent')
                );
        }

        $encoder = $this->getJWTEncoderMock();
        $encoder
            ->expects($this->once())
            ->method('encode')
            ->willReturn('secrettoken');

        $manager = new JWTManager($encoder, $dispatcher, 'username');
        $this->assertEquals('secrettoken', $manager->create(new User('user', 'password')));
    }

    /**
     * test decode.
     */
    public function testDecode()
    {
        $dispatcher = $this->getEventDispatcherMock();

        if ($dispatcher instanceof ContractsEventDispatcherInterface) {
            $dispatcher
                ->expects($this->once())
                ->method('dispatch')
                ->with(
                    $this->isInstanceOf('Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent'),
                    $this->equalTo(Events::JWT_DECODED)
                );
        } else {
            $dispatcher
                ->expects($this->once())
                ->method('dispatch')
                ->with(
                    $this->equalTo(Events::JWT_DECODED),
                    $this->isInstanceOf('Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent')
                );
        }

        $encoder = $this->getJWTEncoderMock();
        $encoder
            ->expects($this->once())
            ->method('decode')
            ->willReturn(['foo' => 'bar']);

        $manager = new JWTManager($encoder, $dispatcher, 'username');
        $this->assertEquals(['foo' => 'bar'], $manager->decode($this->getJWTUserTokenMock()));
    }

    /**
     * test identity field.
     */
    public function testIdentityField()
    {
        $dispatcher = $this->getEventDispatcherMock();

        if ($dispatcher instanceof ContractsEventDispatcherInterface) {
            $dispatcher
                ->expects($this->at(0))
                ->method('dispatch')
                ->with(
                    $this->isInstanceOf('Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent'),
                    $this->equalTo(Events::JWT_CREATED)
                );
            $dispatcher
                ->expects($this->at(1))
                ->method('dispatch')
                ->with(
                    $this->isInstanceOf('Lexik\Bundle\JWTAuthenticationBundle\Event\JWTEncodedEvent'),
                    $this->equalTo(Events::JWT_ENCODED)
                );
        } else {
            $dispatcher
                ->expects($this->at(0))
                ->method('dispatch')
                ->with(
                    $this->equalTo(Events::JWT_CREATED),
                    $this->isInstanceOf('Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent')
                );
            $dispatcher
                ->expects($this->at(1))
                ->method('dispatch')
                ->with(
                    $this->equalTo(Events::JWT_ENCODED),
                    $this->isInstanceOf('Lexik\Bundle\JWTAuthenticationBundle\Event\JWTEncodedEvent')
                );
        }

        $encoder = $this->getJWTEncoderMock();
        $encoder
            ->expects($this->once())
            ->method('encode')
            ->willReturn('secrettoken');

        $manager = new JWTManager($encoder, $dispatcher, 'username');
        $manager->setUserIdentityField('email');
        $this->assertEquals('secrettoken', $manager->create(new CustomUser('user', 'password', 'victuxbb@gmail.com')));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getJWTUserTokenMock()
    {
        $mock = $this
            ->getMockBuilder('Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken')
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->once())
            ->method('getCredentials')
            ->willReturn('secrettoken');

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getJWTEncoderMock()
    {
        return $this
            ->getMockBuilder('Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEventDispatcherMock()
    {
        return $this
            ->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function expectEvent($eventName, $eventClass, $dispatcher)
    {
        if ($dispatcher instanceof ContractsEventDispatcherInterface) {
            $dispatcher->expects($this->once())->method('dispatch')->with($event, $eventName);

            return;
        }

        $dispatcher->expects($this->once())->method('dispatch')->with($eventName, $this->isInstanceOf($eventClass));
    }
}
