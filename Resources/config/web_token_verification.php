<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Jose\Component\Checker\IssuedAtChecker;
use Lexik\Bundle\JWTAuthenticationBundle\Services\WebToken\AccessTokenLoader;
use Psr\Clock\ClockInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('lexik_jwt_authentication.access_token_loader', AccessTokenLoader::class)
        ->private()
        ->args([
            service('Jose\Bundle\JoseFramework\Services\JWSLoaderFactory'),
            service('Jose\Bundle\JoseFramework\Services\JWELoaderFactory')->nullOnInvalid(),
            service('Jose\Bundle\JoseFramework\Services\ClaimCheckerManagerFactory'),
            [], // Claim checkers
            [], // JWS header checkers
            [], // Mandatory claims
            [], // Allowed signature algorithms
            '', // Signature keyset
            false, // Continue on decryption failure
            [], // JWE header checkers
            [], // Allowed key encryption algorithms
            [], // Allowed content encryption algorithms
            null, // Encryption keyset
        ]);

    $services->set('lexik_jwt_authentication.web_token.iat_validator', 'Jose\Component\Checker\IssuedAtChecker')
        ->private()
        ->args([
            '$allowedTimeDrift' => '%lexik_jwt_authentication.clock_skew%',
            '$protectedHeaderOnly' => true,
            '$clock' => service(ClockInterface::class),
        ])
        ->tag('jose.checker.claim', ['alias' => 'iat_with_clock_skew'])
        ->tag('jose.checker.header', ['alias' => 'iat_with_clock_skew']);

    $services->set('lexik_jwt_authentication.web_token.exp_validator', 'Jose\Component\Checker\ExpirationTimeChecker')
        ->private()
        ->args([
            '$allowedTimeDrift' => '%lexik_jwt_authentication.clock_skew%',
            '$protectedHeaderOnly' => true,
            '$clock' => service(ClockInterface::class),
        ])
        ->tag('jose.checker.claim', ['alias' => 'exp_with_clock_skew'])
        ->tag('jose.checker.header', ['alias' => 'exp_with_clock_skew']);

    $services->set('lexik_jwt_authentication.web_token.nbf_validator', 'Jose\Component\Checker\NotBeforeChecker')
        ->private()
        ->args([
            '$allowedTimeDrift' => '%lexik_jwt_authentication.clock_skew%',
            '$protectedHeaderOnly' => true,
            '$clock' => service(ClockInterface::class),
        ])
        ->tag('jose.checker.claim', ['alias' => 'nbf_with_clock_skew'])
        ->tag('jose.checker.header', ['alias' => 'nbf_with_clock_skew']);
};
