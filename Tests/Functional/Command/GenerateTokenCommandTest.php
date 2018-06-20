<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Command;

use Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Security\Core\User\User;

class GenerateTokenCommandTest extends TestCase
{
    public function testRun()
    {
        $tester = new CommandTester((new Application($this->bootKernel(['test_case' => 'GenerateTokenCommand'])))->get('lexik:jwt:generate-token'));

        $this->assertSame(0, $tester->execute(['username' => 'lexik', '--user-class' => User::class]));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The "--user-class" option must be passed as there is more than 1 configured user provider.
     */
    public function testRunWithoutSpecifiedProviderAndMoreThanOneConfigured()
    {
        $tester = new CommandTester((new Application($this->bootKernel(['test_case' => 'GenerateTokenCommand'])))->get('lexik:jwt:generate-token'));

        $this->assertSame(0, $tester->execute(['username' => 'lexik']));
    }
}
