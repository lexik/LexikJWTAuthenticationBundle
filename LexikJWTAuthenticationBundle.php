<?php

namespace Lexik\Bundle\JWTAuthenticationBundle;

use Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\Compiler\WireGenerateTokenCommandPass;
use Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\Security\Factory\JWTFactory;
use Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\Security\Factory\JWTUserFactory;
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

        /** @var SecurityExtension $extension */
        $extension = $container->getExtension('security');

        $extension->addUserProviderFactory(new JWTUserFactory());
        $extension->addSecurityListenerFactory(new JWTFactory()); // BC 1.x, to be removed in 3.0
    }

    /**
     * {@inheritdoc}
     */
    public function registerCommands(Application $application)
    {
        // noop
    }
}
