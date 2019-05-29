<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Command;

use Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateJWTKeyCommandTest extends TestCase
{
    /**
     * @return CommandTester
     */
    final private function getCommandTester()
    {
        $kernel     = $this->bootKernel(['test_case' => 'GenerateJWTKeyCommand']);
        $tester     = new CommandTester((new Application($kernel))->get('lexik:jwt:generate-key'));

        return $tester;
    }

    public function testRun()
    {
        $tester     = $this->getCommandTester();
        $container  = static::$kernel->getContainer();
        $secretKey  = $container->getParameter('lexik_jwt_authentication.secret_key');
        $publicKey  = $container->getParameter('lexik_jwt_authentication.public_key');


        $tester->execute([],['-h']);
        $output = $tester->getDisplay(true);
        $this->assertRegExp('/Generating private key in.*private.pem/', $output);
        $this->assertRegExp('/Generating public key in.*public.pem/', $output);
        $this->assertRegExp('/chmod 0775.*public.pem/', $output);
        $this->assertRegExp('/chmod 0775.*private.pem/', $output);

        $this->assertFileExists($secretKey);
        $this->assertFileExists($publicKey);
    }

    public function testWithInvalidDigest()
    {
        $tester = $this->getCommandTester();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/Invalid.*value foo$/');
        $tester->execute(['--digest' => 'foo']);
    }

    public function testWithInvalidKeyType()
    {
        $tester = $this->getCommandTester();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/Invalid.*key-type.*value foo$/');
        $tester->execute(['--key-type' => 'foo']);
    }
}
