<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Bundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\HttpKernel\Kernel;

class BundleExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        // Annotation must be disabled since this bundle doesn't use Doctrine
        // The framework allows enabling/disabling them only since symfony 3.2 where
        // doctrine/annotations has been removed from required dependencies
        $annotationsEnabled = (int) Kernel::MAJOR_VERSION >= 3 && (int) Kernel::MINOR_VERSION >= 2;

        if (!$annotationsEnabled) {
            return;
        }

        $container->prependExtensionConfig('framework', ['annotations' => ['enabled' => false]]);
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
    }
}
