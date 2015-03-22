<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Security\Authentication\Provider;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Provider\JWTProvider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * JWTProviderTest
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class JWTProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test supports method
     */
    public function testSupports()
    {
        $provider = new JWTProvider($this->getUserProviderMock(), $this->getJWTManagerMock());

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

    /**
     * test authenticate method
     *
     * @expectedException        Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testAuthenticateWithInvalidJWT()
    {
        /** @var TokenInterface $jwtUserToken */
        $jwtUserToken = $this
            ->getMockBuilder('Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken')
            ->disableOriginalConstructor()
            ->getMock();

        $userProvider = $this->getUserProviderMock();

        $jwtManager = $this->getJWTManagerMock();
        $jwtManager->expects($this->any())->method('decode')->will($this->returnValue(false));

        $provider = new JWTProvider($userProvider, $jwtManager);
        $provider->authenticate($jwtUserToken);
    }

    /**
     * test authenticate method
     *
     * @expectedException        Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testAuthenticateWithoutUsername()
    {
        /** @var TokenInterface $jwtUserToken */
        $jwtUserToken = $this
            ->getMockBuilder('Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken')
            ->disableOriginalConstructor()
            ->getMock();

        $userProvider = $this->getUserProviderMock();

        $jwtManager = $this->getJWTManagerMock();
        $jwtManager->expects($this->any())->method('decode')->will($this->returnValue(array('foo' => 'bar')));

        $provider = new JWTProvider($userProvider, $jwtManager);
        $provider->authenticate($jwtUserToken);
    }

    /**
     * test authenticate method
     *
     * @expectedException Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function testAuthenticateWithNotExistingUser()
    {
        /** @var TokenInterface $jwtUserToken */
        $jwtUserToken = $this
            ->getMockBuilder('Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken')
            ->disableOriginalConstructor()
            ->getMock();

        $userProvider = $this->getUserProviderMock();
        $userProvider->expects($this->any())->method('loadUserByUsername')->willThrowException(new UsernameNotFoundException());

        $jwtManager = $this->getJWTManagerMock();
        $jwtManager->expects($this->any())->method('decode')->will($this->returnValue(array('username' => 'user')));

        $provider = new JWTProvider($userProvider, $jwtManager);
        $provider->authenticate($jwtUserToken);
    }

    /**
     * test authenticate method
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

        $user->expects($this->any())->method('getRoles')->will($this->returnValue(array()));

        $userProvider = $this->getUserProviderMock();
        $userProvider->expects($this->any())->method('loadUserByUsername')->will($this->returnValue($user));

        $jwtManager = $this->getJWTManagerMock();
        $jwtManager->expects($this->any())->method('decode')->will($this->returnValue(array('username' => 'user')));

        $provider = new JWTProvider($userProvider, $jwtManager);

        $this->assertInstanceOf(
            'Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken',
            $provider->authenticate($jwtUserToken)
        );

        // test changing user identity field

        $jwtManager = $this->getJWTManagerMock();
        $jwtManager->expects($this->any())->method('decode')->will($this->returnValue(array('uid' => 'user')));

        $provider = new JWTProvider($userProvider, $jwtManager);
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
}
