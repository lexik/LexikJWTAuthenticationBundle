<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Services;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTEncodedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\Token\JWTPostAuthenticationToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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
        $dispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [$this->isInstanceOf(JWTCreatedEvent::class), $this->equalTo(Events::JWT_CREATED)],
                [$this->isInstanceOf(JWTEncodedEvent::class), $this->equalTo(Events::JWT_ENCODED)]
            );

        $encoder = $this->getJWTEncoderMock();
        $encoder
            ->expects($this->once())
            ->method('encode')
            ->willReturn('secrettoken');

        $manager = new JWTManager($encoder, $dispatcher, 'username');
        $this->assertSame('secrettoken', $manager->create($this->createUser()));
    }

    /**
     * test create.
     */
    public function testCreateFromPayload()
    {
        $dispatcher = $this->getEventDispatcherMock();

        $dispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [$this->isInstanceOf(JWTCreatedEvent::class), $this->equalTo(Events::JWT_CREATED)],
                [$this->isInstanceOf(JWTEncodedEvent::class), $this->equalTo(Events::JWT_ENCODED)]
            );

        $encoder = $this->getJWTEncoderMock();
        $encoder
            ->expects($this->once())
            ->method('encode')
            ->willReturn('secrettoken');

        $manager = new JWTManager($encoder, $dispatcher, 'username');
        $payload = ['foo' => 'bar'];
        $this->assertSame('secrettoken', $manager->createFromPayload($this->createUser(), $payload));
    }

    /**
     * test decode.
     */
    public function testDecode()
    {
        $dispatcher = $this->getEventDispatcherMock();
        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(JWTDecodedEvent::class),
                $this->equalTo(Events::JWT_DECODED)
            );

        $encoder = $this->getJWTEncoderMock();
        $encoder
            ->expects($this->once())
            ->method('decode')
            ->willReturn(['foo' => 'bar']);

        $manager = new JWTManager($encoder, $dispatcher, 'username');
        $this->assertSame(['foo' => 'bar'], $manager->decode($this->getJWTUserTokenMock()));
    }

    public function testParse()
    {
        $dispatcher = $this->getEventDispatcherMock();
        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(JWTDecodedEvent::class),
                $this->equalTo(Events::JWT_DECODED)
            );

        $encoder = $this->getJWTEncoderMock();
        $encoder
            ->expects($this->once())
            ->method('decode')
            ->willReturn(['foo' => 'bar']);

        $manager = new JWTManager($encoder, $dispatcher, 'username');
        $this->assertSame(['foo' => 'bar'], $manager->parse('jwt'));
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject&JWTPostAuthenticationToken
     */
    protected function getJWTUserTokenMock()
    {
        $mock = $this->createMock(JWTPostAuthenticationToken::class);

        $mock
            ->expects($this->once())
            ->method('getCredentials')
            ->willReturn('secrettoken');

        return $mock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject&JWTEncoderInterface
     */
    protected function getJWTEncoderMock()
    {
        return $this->createMock(JWTEncoderInterface::class);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject&EventDispatcherInterface
     */
    protected function getEventDispatcherMock()
    {
        return $this->createMock(EventDispatcherInterface::class);
    }

    private function createUser(): UserInterface
    {
        return new InMemoryUser('user', 'password');
    }
}
