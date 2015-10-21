<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional;

/**
 * BootTest
 *
 * @see https://github.com/FriendsOfSymfony/FOSRestBundle/tree/master/Tests/Functional
 */
class BootTest extends TestCase
{
    public function testBoot()
    {
        $kernel = $this->createKernel();
        $kernel->boot();
    }
}
