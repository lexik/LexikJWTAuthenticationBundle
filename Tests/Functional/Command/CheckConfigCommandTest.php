<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Command;

use Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
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
        $kernel = $this->bootKernel();
        $app = new Application($kernel);
        $tester = new CommandTester($app->find('lexik:jwt:check-config'));

        $this->assertSame(0, $tester->execute([]));
        $this->{method_exists($this, 'assertStringContainsString') ? 'assertStringContainsString' : 'assertContains'}('The configuration seems correct.', $tester->getDisplay());
    }
}
