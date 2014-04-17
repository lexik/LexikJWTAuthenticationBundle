<?php

namespace Security\Authentication\Provider;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Provider\JWTProvider;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;

/**
 * JWTProviderTest
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class JWTProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JWTProvider
     */
    protected $provider;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $userProvider = new InMemoryUserProvider(array(
            'user' => array(
                'password' => 'userpass'
            )
        ));

        $decoder = $this->getMockBuilder('Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoder')
            ->disableOriginalConstructor()
            ->getMock();

        $decoder
            ->expects($this->any())
            ->method('decode')
            ->will($this->returnValue(array('username' => 'user')));

        $this->provider = new JWTProvider($userProvider, $decoder);
    }

    public function testSupports()
    {
        $usernamePasswordToken = $this
            ->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertFalse($this->provider->supports($usernamePasswordToken));

        $jwtUserToken = $this
            ->getMockBuilder('Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertTrue($this->provider->supports($jwtUserToken));
    }
} 
