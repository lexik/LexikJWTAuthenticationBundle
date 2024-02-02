<?php

namespace Functional\Command;

use Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group web-token
 */
class EnableEncryptionConfigCommandTest extends TestCase
{
    /**
     * Test command.
     */
    public function testMigrationTool()
    {
        // Given
        $kernel = $this->bootKernel(['test_case' => 'WebTokenWithoutEncryption']);
        $app = new Application($kernel);
        $tester = new CommandTester($app->find('lexik:jwt:enable-encryption'));

        // When
        $tester->setInputs(['A256GCMKW', 'A256GCM']);
        $statusCode = $tester->execute([]);

        // Then
        $this->assertSame(Command::SUCCESS, $statusCode);
        $this->assertStringContainsString('service: lexik_jwt_authentication.encoder.web_token', $tester->getDisplay());
        $this->assertStringContainsString('signature_algorithm: RS256', $tester->getDisplay());
        $this->assertStringContainsString('key_encryption_algorithm: A256GCMKW', $tester->getDisplay());
        $this->assertStringContainsString('content_encryption_algorithm: A256GCM', $tester->getDisplay());
    }
}
