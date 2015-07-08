<?php

namespace Services;

use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Symfony\Component\Security\Core\User\User;
use Lexik\Bundle\JWTAuthenticationBundle\Tests\Stubs\User as CustomUser;

/**
 * JWTManagerTest
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class JWTManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test create
     */
    public function testCreate()
    {
        $dispatcher = $this->getEventDispatcherMock();
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

        $encoder = $this->getJWTEncoderMock();
        $encoder
            ->expects($this->once())
            ->method('encode')
            ->willReturn('secrettoken');

        $manager = new JWTManager($encoder, $dispatcher, 3600);
        $this->assertEquals('secrettoken', $manager->create(new User('user', 'password')));
    }

    /**
     * test decode
     */
    public function testDecode()
    {
        $dispatcher = $this->getEventDispatcherMock();
        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo(Events::JWT_DECODED),
                $this->isInstanceOf('Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent')
            );

        $encoder = $this->getJWTEncoderMock();
        $encoder
            ->expects($this->once())
            ->method('decode')
            ->willReturn(array('foo' => 'bar'));

        $manager = new JWTManager($encoder, $dispatcher, 3600);
        $this->assertEquals(array('foo' => 'bar'), $manager->decode($this->getJWTUserTokenMock()));
    }

    /**
     * test identity field
     */
    public function testIdentityField()
    {
        
        $dispatcher = $this->getEventDispatcherMock();
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

        $encoder = $this->getJWTEncoderMock();
        $encoder
            ->expects($this->once())
            ->method('encode')
            ->willReturn('secrettoken');

        $manager = new JWTManager($encoder, $dispatcher, 3600);
        $manager->setUserIdentityField("email");
        $this->assertEquals('secrettoken', $manager->create(new CustomUser('user', 'password','victuxbb@gmail.com')));
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
            ->getMockBuilder('Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoder')
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
}
