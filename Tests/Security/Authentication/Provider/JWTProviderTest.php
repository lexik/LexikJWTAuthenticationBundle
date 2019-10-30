<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Security\Authentication\Provider;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Provider\JWTProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * JWTProviderTest.
 *
 * @group legacy
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class JWTProviderTest extends TestCase
{
    /**
     * test supports method.
     */
    public function testSupports()
    {
        $provider = new JWTProvider($this->getUserProviderMock(), $this->getJWTManagerMock(), $this->getEventDispatcherMock(), 'username');

        /** @var TokenInterface $usernamePasswordToken */
        $usernamePasswordToken = $this
            ->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertFalse($provider->supports($usernamePasswordToken));

        /** @var TokenInterface $jwtUserToken */
        $jwtUserToken = $this
            ->getMockBuilder('Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertTrue($provider->supports($jwtUserToken));
    }

    public function testAuthenticateWithInvalidJWT()
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid JWT Token');

        /** @var TokenInterface $jwtUserToken */
        $jwtUserToken = $this
            ->getMockBuilder('Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken')
            ->disableOriginalConstructor()
            ->getMock();

        $userProvider    = $this->getUserProviderMock();
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

        /** @var TokenInterface $jwtUserToken */
        $jwtUserToken = $this
            ->getMockBuilder('Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken')
            ->disableOriginalConstructor()
            ->getMock();

        $userProvider    = $this->getUserProviderMock();
        $eventDispatcher = $this->getEventDispatcherMock();

        $jwtManager = $this->getJWTManagerMock();
        $jwtManager->expects($this->any())->method('decode')->will($this->returnValue(['foo' => 'bar']));

        $provider = new JWTProvider($userProvider, $jwtManager, $eventDispatcher, 'username');
        $provider->authenticate($jwtUserToken);
    }

    public function testAuthenticateWithNotExistingUser()
    {
        $this->expectException(UsernameNotFoundException::class);

        /** @var TokenInterface $jwtUserToken */
        $jwtUserToken = $this
            ->getMockBuilder('Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken')
            ->disableOriginalConstructor()
            ->getMock();

        $userProvider = $this->getUserProviderMock();
        $userProvider->expects($this->any())->method('loadUserByUsername')->willThrowException(new UsernameNotFoundException());

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
        /** @var TokenInterface $jwtUserToken */
        $jwtUserToken = $this
            ->getMockBuilder('Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken')
            ->disableOriginalConstructor()
            ->getMock();

        $user = $this
            ->getMockBuilder('Symfony\Component\Security\Core\User\UserInterface')
            ->getMock();

        $user->expects($this->any())->method('getRoles')->will($this->returnValue([]));

        $userProvider = $this->getUserProviderMock();
        $userProvider->expects($this->any())->method('loadUserByUsername')->will($this->returnValue($user));

        $eventDispatcher = $this->getEventDispatcherMock();

        $jwtManager = $this->getJWTManagerMock();
        $jwtManager->expects($this->any())->method('decode')->will($this->returnValue(['username' => 'user']));

        $provider = new JWTProvider($userProvider, $jwtManager, $eventDispatcher, 'username');

        $this->assertInstanceOf(
            'Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken',
            $provider->authenticate($jwtUserToken)
        );

        // test changing user identity field

        $jwtManager = $this->getJWTManagerMock();
        $jwtManager->expects($this->any())->method('decode')->will($this->returnValue(['uid' => 'user']));

        $provider = new JWTProvider($userProvider, $jwtManager, $eventDispatcher, 'uid');
        $provider->setUserIdentityField('uid');

        $this->assertInstanceOf(
            'Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken',
            $provider->authenticate($jwtUserToken)
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getJWTManagerMock()
    {
        return $this->getMockBuilder('Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getJWTEncoderMock()
    {
        return $this->getMockBuilder('Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getUserProviderMock()
    {
        return $this->getMockBuilder('Symfony\Component\Security\Core\User\InMemoryUserProvider')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEventDispatcherMock()
    {
        return $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
