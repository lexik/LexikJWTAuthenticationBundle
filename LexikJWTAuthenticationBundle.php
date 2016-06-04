<?php

namespace Lexik\Bundle\JWTAuthenticationBundle;

use Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\Security\Factory\JWTFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
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

        /** @var SecurityExtension $extension */
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new JWTFactory());
    }
}
