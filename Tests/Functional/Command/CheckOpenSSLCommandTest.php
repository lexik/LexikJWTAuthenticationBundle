<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Command;

use Lexik\Bundle\JWTAuthenticationBundle\Command\CheckOpenSSLCommand;
use Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * CheckOpenSSLCommandTest
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class CheckOpenSSLCommandTest extends TestCase
{
    /**
     * Test command
     */
    public function testCheckOpenSSLCommand()
    {
        $kernel = $this->createKernel();
        $kernel->boot();

        $command = new CheckOpenSSLCommand();
        $command->setContainer($kernel->getContainer());

        $tester = new CommandTester($command);
        $result = $tester->execute(array());

        $this->assertEquals(0, $result);
        $this->assertEquals('OpenSSL configuration seems correct.' . PHP_EOL, $tester->getDisplay());
    }
}
