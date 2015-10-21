<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * AppKernel
 */
class AppKernel extends Kernel
{
    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        return array(
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir()
    {
        return sys_get_temp_dir().'/LexikJWTAuthenticationBundle/';
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir()
    {
        return sys_get_temp_dir().'/LexikJWTAuthenticationBundle/';
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config.yml');
    }
}
