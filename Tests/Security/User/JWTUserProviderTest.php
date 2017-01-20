<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Security\User;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUser;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserProvider;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * JWTProviderTest.
 *
 * @group legacy
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class JWTUserProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testSupportsClass()
    {
        $userProvider = new JWTUserProvider(JWTUser::class);

        $this->assertTrue($userProvider->supportsClass(JWTUserInterface::class));
        $this->assertTrue($userProvider->supportsClass(JWTUser::class));
        $this->assertFalse($userProvider->supportsClass(UserInterface::class));
    }

    public function testLoadUserByUsername()
    {
        $userProvider = new JWTUserProvider(JWTUser::class);
        $user         = $userProvider->loadUserByUsername('lexik');

        $this->assertInstanceOf(JWTUser::class, $user);
        $this->assertSame('lexik', $user->getUsername());

        $this->assertSame($userProvider->loadUserByUsername('lexik'), $user, 'User instances should be cached.');
    }

    public function testRefreshUser()
    {
        $user = new JWTUser('lexik');
        $this->assertSame($user, (new JWTUserProvider(JWTUser::class))->refreshUser($user));
    }
}
