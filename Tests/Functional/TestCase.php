<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * TestCase
 */
abstract class TestCase extends WebTestCase
{
    /**
     * {@inheritdoc}
     */
    protected static function createKernel(array $options = array())
    {
        return new AppKernel('test', true);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $fs = new Filesystem();
        $fs->remove(sys_get_temp_dir().'/LexikJWTAuthenticationBundle/');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        static::$kernel = null;
    }
}
