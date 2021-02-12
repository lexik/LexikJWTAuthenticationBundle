<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Command;

use Lexik\Bundle\JWTAuthenticationBundle\Command\GenerateKeyPairCommand;
use Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

class GenerateKeyPairCommandTest extends TestCase
{
    public function testCannotGenerateKeysWhenPathsAreNull()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The "lexik_jwt_authentication.secret_key" and "lexik_jwt_authentication.public_key" config options must not be empty for using the "lexik:jwt:generate-keypair" command.');

        $command = new GenerateKeyPairCommand(new Filesystem(), null, null, null, 'RS512');

        (new CommandTester($command))->execute([]);
    }

    /**
     * @dataProvider providePassphrase
     */
    public function testItGeneratesKeyPair($algorithm, $passphrase)
    {
        $privateKeyFile = \tempnam(\sys_get_temp_dir(), 'private_');
        $publicKeyFile = \tempnam(\sys_get_temp_dir(), 'public_');

        // tempnam() actually create the files, but we have to simulate they don't exist
        \unlink($privateKeyFile);
        \unlink($publicKeyFile);

        $tester = new CommandTester(
            new GenerateKeyPairCommand(
                new Filesystem(),
                $privateKeyFile,
                $publicKeyFile,
                $passphrase,
                $algorithm
            )
        );

        $returnCode = $tester->execute([], ['interactive' => false]);
        $this->assertSame(0, $returnCode);

        $privateKey = \file_get_contents($privateKeyFile);
        $publicKey = \file_get_contents($publicKeyFile);
        $this->assertStringContainsString('Done!', $tester->getDisplay(true));
        $this->assertNotFalse($privateKey);
        $this->assertNotFalse($publicKey);
        $this->assertStringContainsString('PRIVATE KEY', $privateKey);
        $this->assertStringContainsString('PUBLIC KEY', $publicKey);

        // Encryption / decryption test
        /*$payload = 'Despite the constant negative press covfefe';
        \openssl_public_encrypt($payload, $encryptedData, \openssl_pkey_get_public($publicKey));
        \openssl_private_decrypt($encryptedData, $decryptedData, \openssl_pkey_get_private($privateKey, $passphrase));
        $this->assertSame($payload, $decryptedData);*/

    }

    public function providePassphrase()
    {
        yield ['RS256', null];
        yield ['RS384', null];
        yield ['RS512', null];
        yield ['HS256', null];
        yield ['HS384', null];
        yield ['HS512', null];
        yield ['ES256', null];
        yield ['ES384', null];
        yield ['ES512', null];
        yield ['RS256', 'dummy'];
        yield ['RS384', 'dummy'];
        yield ['RS512', 'dummy'];
        yield ['HS256', 'dummy'];
        yield ['HS384', 'dummy'];
        yield ['HS512', 'dummy'];
        yield ['ES256', 'dummy'];
        yield ['ES384', 'dummy'];
        yield ['ES512', 'dummy'];
    }

    public function testOverwriteAndSkipCannotBeCombined()
    {
        $privateKeyFile = \tempnam(\sys_get_temp_dir(), 'private_');
        $publicKeyFile = \tempnam(\sys_get_temp_dir(), 'public_');

        \file_put_contents($privateKeyFile, 'foobar');
        \file_put_contents($publicKeyFile, 'foobar');

        $tester = new CommandTester(
            new GenerateKeyPairCommand(
                new Filesystem(),
                $privateKeyFile,
                $publicKeyFile,
                null,
                'RS256'
            )
        );
        $input = ['--overwrite' => true, '--skip-if-exists' => true];
        $returnCode = $tester->execute($input, ['interactive' => false]);
        $this->assertSame(1, $returnCode);
        $this->assertStringContainsString(
            'Both options `--skip-if-exists` and `--overwrite` cannot be combined.',
            $tester->getDisplay(true)
        );

        $privateKey = \file_get_contents($privateKeyFile);
        $publicKey = \file_get_contents($publicKeyFile);
        $this->assertStringContainsString('foobar', $privateKey);
        $this->assertStringContainsString('foobar', $publicKey);
    }

    public function testNoOverwriteDoesNotOverwrite()
    {
        $privateKeyFile = \tempnam(\sys_get_temp_dir(), 'private_');
        $publicKeyFile = \tempnam(\sys_get_temp_dir(), 'public_');

        \file_put_contents($privateKeyFile, 'foobar');
        \file_put_contents($publicKeyFile, 'foobar');

        $tester = new CommandTester(
            new GenerateKeyPairCommand(
                new Filesystem(),
                $privateKeyFile,
                $publicKeyFile,
                null,
                'RS256'
            )
        );

        $returnCode = $tester->execute([], ['interactive' => false]);
        $this->assertSame(1, $returnCode);
        $this->assertStringContainsString(
            'Your keys already exist. Use the `--overwrite` option to force regeneration.',
            \preg_replace('/\s+/', ' ', $tester->getDisplay(true))
        );

        $privateKey = \file_get_contents($privateKeyFile);
        $publicKey = \file_get_contents($publicKeyFile);
        $this->assertStringContainsString('foobar', $privateKey);
        $this->assertStringContainsString('foobar', $publicKey);
    }

    public function testOverwriteActuallyOverwrites()
    {
        $privateKeyFile = \tempnam(\sys_get_temp_dir(), 'private_');
        $publicKeyFile = \tempnam(\sys_get_temp_dir(), 'public_');

        \file_put_contents($privateKeyFile, 'foobar');
        \file_put_contents($publicKeyFile, 'foobar');

        $tester = new CommandTester(
            new GenerateKeyPairCommand(
                new Filesystem(),
                $privateKeyFile,
                $publicKeyFile,
                null,
                'RS256'
            )
        );

        $returnCode = $tester->execute(['--overwrite' => true], ['interactive' => false]);
        $privateKey = \file_get_contents($privateKeyFile);
        $publicKey = \file_get_contents($publicKeyFile);

        $this->assertSame(0, $returnCode);
        $this->assertStringContainsString('PRIVATE KEY', $privateKey);
        $this->assertStringContainsString('PUBLIC KEY', $publicKey);
    }

    public function testSkipIfExistsWritesIfNotExists()
    {
        $privateKeyFile = \tempnam(\sys_get_temp_dir(), 'private_');
        $publicKeyFile = \tempnam(\sys_get_temp_dir(), 'public_');

        // tempnam() actually create the files, but we have to simulate they don't exist
        \unlink($privateKeyFile);
        \unlink($publicKeyFile);

        $tester = new CommandTester(
            new GenerateKeyPairCommand(
                new Filesystem(),
                $privateKeyFile,
                $publicKeyFile,
                null,
                'RS256'
            )
        );

        $this->assertSame(0, $tester->execute(['--skip-if-exists' =>  true], ['interactive' => false]));
        $this->assertStringContainsString('Done!', $tester->getDisplay(true));
        $privateKey = \file_get_contents($privateKeyFile);
        $publicKey = \file_get_contents($publicKeyFile);
        $this->assertStringContainsString('PRIVATE KEY', $privateKey);
        $this->assertStringContainsString('PUBLIC KEY', $publicKey);
    }

    public function testSkipIfExistsDoesNothingIfExists()
    {
        $privateKeyFile = \tempnam(\sys_get_temp_dir(), 'private_');
        $publicKeyFile = \tempnam(\sys_get_temp_dir(), 'public_');

        \file_put_contents($privateKeyFile, 'foobar');
        \file_put_contents($publicKeyFile, 'foobar');

        $tester = new CommandTester(
            new GenerateKeyPairCommand(
                new Filesystem(),
                $privateKeyFile,
                $publicKeyFile,
                null,
                'RS256'
            )
        );

        $this->assertSame(0, $tester->execute(['--skip-if-exists' =>  true], ['interactive' => false]));
        $this->assertStringContainsString(
            'Your key files already exist, they won\'t be overriden.',
            $tester->getDisplay(true)
        );

        $privateKey = \file_get_contents($privateKeyFile);
        $publicKey = \file_get_contents($publicKeyFile);
        $this->assertStringContainsString('foobar', $privateKey);
        $this->assertStringContainsString('foobar', $publicKey);
    }

}
