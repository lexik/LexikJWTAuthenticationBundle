<?php

namespace Lexik\Bundle\JWTAuthenticationBundle;

use Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\Compiler\DeprecateLegacyGuardAuthenticatorPass;
use Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\Compiler\RegisterLegacyGuardAuthenticatorPass;
use Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\Compiler\WireGenerateTokenCommandPass;
use Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\Security\Factory\JWTAuthenticatorFactory;
use Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\Security\Factory\JWTFactory;
use Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\Security\Factory\JWTUserFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AuthenticatorFactoryInterface;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * LexikJWTAuthenticationBundle.
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class LexikJWTAuthenticationBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new WireGenerateTokenCommandPass());
        $container->addCompilerPass(new DeprecateLegacyGuardAuthenticatorPass());

        /** @var SecurityExtension $extension */
        $extension = $container->getExtension('security');

        $extension->addUserProviderFactory(new JWTUserFactory());
        $extension->addSecurityListenerFactory(new JWTFactory(false)); // BC 1.x, to be removed in 3.0
        if (interface_exists(AuthenticatorFactoryInterface::class)) {
            $extension->addSecurityListenerFactory(new JWTAuthenticatorFactory());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function registerCommands(Application $application)
    {
        // noop
    }
}
