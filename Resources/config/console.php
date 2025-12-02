<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Lexik\Bundle\JWTAuthenticationBundle\Command\CheckConfigCommand;
use Lexik\Bundle\JWTAuthenticationBundle\Command\EnableEncryptionConfigCommand;
use Lexik\Bundle\JWTAuthenticationBundle\Command\GenerateKeyPairCommand;
use Lexik\Bundle\JWTAuthenticationBundle\Command\GenerateTokenCommand;
use Lexik\Bundle\JWTAuthenticationBundle\Command\MigrateConfigCommand;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('lexik_jwt_authentication.check_config_command', CheckConfigCommand::class)
        ->args([
            service('lexik_jwt_authentication.key_loader'),
            '%lexik_jwt_authentication.encoder.signature_algorithm%',
        ])
        ->tag('console.command', ['command' => 'lexik:jwt:check-config']);

    $services->set('lexik_jwt_authentication.migrate_config_command', MigrateConfigCommand::class)
        ->args([
            service('lexik_jwt_authentication.key_loader'),
            '%lexik_jwt_authentication.pass_phrase%',
            '%lexik_jwt_authentication.encoder.signature_algorithm%',
        ])
        ->tag('console.command', ['command' => 'lexik:jwt:migrate-config']);

    $services->set('lexik_jwt_authentication.enable_encryption_config_command', EnableEncryptionConfigCommand::class)
        ->args([service('Jose\Component\Core\AlgorithmManagerFactory')->nullOnInvalid()])
        ->tag('console.command', ['command' => 'lexik:jwt:enable-encryption']);

    $services->set('lexik_jwt_authentication.generate_token_command', GenerateTokenCommand::class)
        ->public()
        ->args([
            service('lexik_jwt_authentication.jwt_manager'),
            [], // user providers
        ])
        ->tag('console.command', ['command' => 'lexik:jwt:generate-token']);

    $services->set('lexik_jwt_authentication.generate_keypair_command', GenerateKeyPairCommand::class)
        ->args([
            service('filesystem'),
            '',
            '',
            '',
            '',
        ])
        ->tag('console.command', ['command' => 'lexik:jwt:generate-keypair']);
};
