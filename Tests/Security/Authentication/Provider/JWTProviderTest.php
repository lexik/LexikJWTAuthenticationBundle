<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Security\Authentication\Provider;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Provider\JWTProvider;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * JWTProviderTest.
 *
 * @group legacy
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class JWTProviderTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!interface_exists(AuthenticationProviderInterface::class)) {
            self::markTestSkipped('Test only applies to symfony/security-core 5.4 and earlier');
        }
    }

    /**
     * test supports method.
     */
    public function testSupports()
    {
        $provider = new JWTProvider($this->getUserProviderMock(), $this->getJWTManagerMock(), $this->getEventDispatcherMock(), 'username');

        $usernamePasswordToken = $this
            ->getMockBuilder(UsernamePasswordToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertFalse($provider->supports($usernamePasswordToken));

        $jwtUserToken = $this
            ->getMockBuilder(JWTUserToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertTrue($provider->supports($jwtUserToken));
    }

    public function testAuthenticateWithInvalidJWT()
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid JWT Token');

        $jwtUserToken = $this
            ->getMockBuilder(JWTUserToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userProvider = $this->getUserProviderMock();
        $eventDispatcher = $this->getEventDispatcherMock();

        $jwtManager = $this->getJWTManagerMock();
        $jwtManager->expects($this->any())->method('decode')->will($this->returnValue(false));

        $provider = new JWTProvider($userProvider, $jwtManager, $eventDispatcher, 'username');
        $provider->authenticate($jwtUserToken);
    }

    public function testAuthenticateWithoutUsername()
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid JWT Token');

        $jwtUserToken = $this
            ->getMockBuilder(JWTUserToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userProvider = $this->getUserProviderMock();
        $eventDispatcher = $this->getEventDispatcherMock();

        $jwtManager = $this->getJWTManagerMock();
        $jwtManager->expects($this->any())->method('decode')->will($this->returnValue(['foo' => 'bar']));

        $provider = new JWTProvider($userProvider, $jwtManager, $eventDispatcher, 'username');
        $provider->authenticate($jwtUserToken);
    }

    public function testAuthenticateWithNotExistingUser()
    {
        if (class_exists(UserNotFoundException::class)) {
            $this->expectException(UserNotFoundException::class);
        } else {
            $this->expectException(UsernameNotFoundException::class);
        }

        $jwtUserToken = $this
            ->getMockBuilder(JWTUserToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userProvider = $this->getUserProviderMock();
        $userProvider->expects($this->any())->method($this->getUserProviderLoadMethodName())->willThrowException(new UsernameNotFoundException());

        $eventDispatcher = $this->getEventDispatcherMock();

        $jwtManager = $this->getJWTManagerMock();
        $jwtManager->expects($this->any())->method('decode')->will($this->returnValue(['username' => 'user']));

        $provider = new JWTProvider($userProvider, $jwtManager, $eventDispatcher, 'username');
        $provider->authenticate($jwtUserToken);
    }

    /**
     * test authenticate method.
     */
    public function testAuthenticate()
    {
        $jwtUserToken = $this
            ->getMockBuilder(JWTUserToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $user = $this
            ->getMockBuilder(UserInterface::class)
            ->getMock();

        $user->expects($this->any())->method('getRoles')->will($this->returnValue([]));

        $userProvider = $this->getUserProviderMock();
        $userProvider->expects($this->any())->method($this->getUserProviderLoadMethodName())->will($this->returnValue($user));

        $eventDispatcher = $this->getEventDispatcherMock();

        $jwtManager = $this->getJWTManagerMock();
        $jwtManager->expects($this->any())->method('decode')->will($this->returnValue(['username' => 'user']));

        $provider = new JWTProvider($userProvider, $jwtManager, $eventDispatcher, 'username');

        $this->assertInstanceOf(
            JWTUserToken::class,
            $provider->authenticate($jwtUserToken)
        );

        $jwtManager = $this->getJWTManagerMock();
        $jwtManager->expects($this->any())->method('decode')->will($this->returnValue(['uid' => 'user']));

        $provider = new JWTProvider($userProvider, $jwtManager, $eventDispatcher, 'uid');
        $provider->setUserIdentityField('uid');

        $this->assertInstanceOf(
            JWTUserToken::class,
            $provider->authenticate($jwtUserToken)
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getJWTManagerMock()
    {
        return $this->getMockBuilder(JWTManager::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getJWTEncoderMock()
    {
        return $this->getMockBuilder(JWTEncoderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getUserProviderMock()
    {
        return $this->getMockBuilder(InMemoryUserProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return string
     */
    protected function getUserProviderLoadMethodName()
    {
        if (method_exists(InMemoryUserProvider::class, 'loadUserByIdentifier')) {
            return 'loadUserByIdentifier';
        }

        return 'loadUserByUsername';
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEventDispatcherMock()
    {
        return $this->getMockBuilder(EventDispatcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
