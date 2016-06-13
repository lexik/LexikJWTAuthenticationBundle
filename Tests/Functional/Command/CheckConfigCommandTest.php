<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Command;

use Lexik\Bundle\JWTAuthenticationBundle\Command\CheckConfigCommand;
use Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * CheckOpenSSLCommandTest.
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class CheckConfigCommandTest extends TestCase
{
    /**
     * Test command.
     */
    public function testCheckOpenSSLCommand()
    {
        $kernel = $this->createKernel();
        $kernel->boot();

        $command = new CheckConfigCommand();
        $command->setContainer($kernel->getContainer());

        $tester = new CommandTester($command);
        $this->assertEquals(0, $tester->execute([]));
        $this->assertContains('The configuration seems correct.', $tester->getDisplay());
    }
}
