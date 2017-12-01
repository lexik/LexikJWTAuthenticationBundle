<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Command;

use Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class GenKeysCommandTest extends TestCase
{
    public function testGenKeysCommand()
    {
        $kernel = $this->createKernel();
        $kernel->boot();

        $tester = new CommandTester($kernel->getContainer()->get('lexik_jwt_authentication.gen_keys_command'));
        $this->assertEquals(0, $tester->execute([]));
        $this->assertContains('Keys successfully generated', $tester->getDisplay());
    }
}
