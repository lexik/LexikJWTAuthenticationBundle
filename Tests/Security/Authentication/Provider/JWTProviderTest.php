<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Security\Authentication\Provider;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Provider\JWTProvider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

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
        $provider = new JWTProvider($this->getUserProviderMock(), $this->getJWTEncoderMock());

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
     */
    public function testAuthenticate()
    {
        /** @var TokenInterface $jwtUserToken */
        $jwtUserToken = $this
            ->getMockBuilder('Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken')
            ->disableOriginalConstructor()
            ->getMock();

        $jwtEncoder = $this->getJWTEncoderMock();
        $jwtEncoder->expects($this->any())->method('decode')->will($this->returnValue(array('username' => 'user')));

        $user = $this
            ->getMockBuilder('Symfony\Component\Security\Core\User\UserInterface')
            ->getMock();

        $user->expects($this->any())->method('getRoles')->will($this->returnValue(array()));

        $userProvider = $this->getUserProviderMock();
        $userProvider->expects($this->any())->method('loadUserByUsername')->will($this->returnValue($user));

        $provider = new JWTProvider($userProvider, $jwtEncoder);

        $this->assertInstanceOf(
            'Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken',
            $provider->authenticate($jwtUserToken)
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getJWTEncoderMock()
    {
        return $this->getMockBuilder('Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoder')
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
