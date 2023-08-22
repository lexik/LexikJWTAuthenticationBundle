<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Command;

use Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\TestCase;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Security\Core\User\InMemoryUser;

class GenerateTokenCommandTest extends TestCase
{
    public function testRun()
    {
        $tester = new CommandTester((new Application($this->bootKernel(['test_case' => 'GenerateTokenCommand'])))->get('lexik:jwt:generate-token'));

        $this->assertSame(0, $tester->execute(['username' => 'lexik', '--user-class' => InMemoryUser::class]));
    }

    public function testRunWithoutSpecifiedProviderAndMoreThanOneConfigured()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The "--user-class" option must be passed as there is more than 1 configured user provider.');

        $tester = new CommandTester((new Application($this->bootKernel(['test_case' => 'GenerateTokenCommand'])))->get('lexik:jwt:generate-token'));

        $this->assertSame(0, $tester->execute(['username' => 'lexik']));
    }
}
