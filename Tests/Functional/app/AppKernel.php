<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional;

use Psr\Log\NullLogger;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

/**
 * AppKernel.
 */
class AppKernel extends Kernel
{
    private $encoder;

    private $userProvider;

    private $signatureAlgorithm;

    private $testCase;

    public function __construct($environment, $debug, $testCase = null)
    {
        parent::__construct($environment, $debug);


        $this->testCase           = $testCase;
        $this->encoder            = getenv('ENCODER') ?: 'default';
        $this->userProvider       = getenv('PROVIDER') ?: 'in_memory';
        $this->signatureAlgorithm = getenv('ALGORITHM');
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle(),
            new \Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Bundle\Bundle(),
        ];
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir()
    {
        return sys_get_temp_dir().'/LexikJWTAuthenticationBundle/cache';
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir()
    {
        return sys_get_temp_dir().'/LexikJWTAuthenticationBundle/logs';
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        if ($this->testCase && file_exists(__DIR__.'/config/'.$this->testCase.'/config.yml')) {
            $loader->load(__DIR__.'/config/'.$this->testCase.'/config.yml');

            return;
        }

        $loader->load(__DIR__.sprintf('/config/security_%s.yml', $this->userProvider));

        if ($this->signatureAlgorithm && file_exists($file = __DIR__.sprintf('/config/config_%s_%s.yml', $this->encoder, strtolower($this->signatureAlgorithm)))) {
            $loader->load($file);

            return;
        }

        $loader->load(__DIR__.sprintf('/config/config_%s.yml', $this->encoder));
    }

    public function getUserProvider()
    {
        return $this->userProvider;
    }

    public function getEncoder()
    {
        return $this->encoder;
    }

    protected function build(ContainerBuilder $container)
    {
        $container->register('logger', NullLogger::class);
    }
}
