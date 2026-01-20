<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Services;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTEncodedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\Token\JWTPostAuthenticationToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\PayloadEnrichmentInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\TestBrowserToken;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
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

    public function testCreateWithPayloadEnrichment()
    {
        $dispatcher = $this->getEventDispatcherMock();
        $encoder = $this->getJWTEncoderMock();
        $encoder
            ->method('encode')
            ->with($this->arrayHasKey('baz'))
            ->willReturn('secrettoken');

        $manager = new JWTManager($encoder, $dispatcher, 'username', new class() implements PayloadEnrichmentInterface {
            public function enrich(UserInterface $user, array &$payload): void
            {
                $payload['baz'] = 'qux';
            }
        });

        $this->assertEquals('secrettoken', $manager->create($this->createUser()));
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

    public function testCreateFromPayloadWithPayloadEnrichment()
    {
        $dispatcher = $this->getEventDispatcherMock();

        $encoder = $this->getJWTEncoderMock();
        $encoder
            ->method('encode')
            ->with($this->arrayHasKey('baz'))
            ->willReturn('secrettoken');

        $manager = new JWTManager($encoder, $dispatcher, 'username', new class() implements PayloadEnrichmentInterface {
            public function enrich(UserInterface $user, array &$payload): void
            {
                $payload['baz'] = 'qux';
            }
        });
        $payload = ['foo' => 'bar'];
        $this->assertEquals('secrettoken', $manager->createFromPayload($this->createUser(), $payload));
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

    /**
     * test decode a TokenInterface without getCredentials.
     */
    public function testDecode_noGetCredentials()
    {
        $dispatcher = $this->getEventDispatcherMock();
        $dispatcher
            ->expects($this->never())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(JWTDecodedEvent::class),
                $this->equalTo(Events::JWT_DECODED)
            );

        $encoder = $this->getJWTEncoderMock();
        $encoder
            ->expects($this->never())
            ->method('decode')
            ->willReturn(['foo' => 'bar']);

        $token = new NullToken();
        $manager = new JWTManager($encoder, $dispatcher, 'username');
        $this->assertFalse($manager->decode($token));
    }

    /**
     * test decode a TokenInterface with getCredentials returning null.
     */
    public function testDecode_nullGetCredentials()
    {
        $dispatcher = $this->getEventDispatcherMock();
        $dispatcher
            ->expects($this->never())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(JWTDecodedEvent::class),
                $this->equalTo(Events::JWT_DECODED)
            );

        $encoder = $this->getJWTEncoderMock();
        $encoder
            ->expects($this->never())
            ->method('decode')
            ->willReturn(['foo' => 'bar']);

        $token = new TestBrowserToken();
        $manager = new JWTManager($encoder, $dispatcher, 'username');
        $this->assertFalse($manager->decode($token));
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
     * @return MockObject&JWTPostAuthenticationToken
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
     * @return MockObject&JWTEncoderInterface
     */
    protected function getJWTEncoderMock()
    {
        return $this->createMock(JWTEncoderInterface::class);
    }

    /**
     * @return MockObject&EventDispatcherInterface
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
