<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserProvider;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\ChainTokenExtractor;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\CookieTokenExtractor;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\QueryParameterTokenExtractor;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\SplitCookieExtractor;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('lexik_jwt_authentication.extractor.chain_extractor', ChainTokenExtractor::class)
        ->private()
        ->args([[]]);

    $services->set('lexik_jwt_authentication.extractor.authorization_header_extractor', AuthorizationHeaderTokenExtractor::class)
        ->args([
            '', // Header Value Prefix
            '', // Header Value Name
        ]);

    $services->set('lexik_jwt_authentication.extractor.query_parameter_extractor', QueryParameterTokenExtractor::class)
        ->args([
            '', // Parameter Name
        ]);

    $services->set('lexik_jwt_authentication.extractor.cookie_extractor', CookieTokenExtractor::class)
        ->args([
            '', // Name
        ]);

    $services->set('lexik_jwt_authentication.extractor.split_cookie_extractor', SplitCookieExtractor::class)
        ->args([
            [], // Cookies
        ]);

    $services->set('lexik_jwt_authentication.security.jwt_user_provider', JWTUserProvider::class)
        ->private()
        ->args([
            '',
        ]);

    $services->alias(TokenExtractorInterface::class, 'lexik_jwt_authentication.extractor.chain_extractor');
};
