<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * TestCase.
 */
abstract class TestCase extends WebTestCase
{
    protected static $client;

    /**
     * {@inheritdoc}
     */
    protected static function createKernel(array $options = [])
    {
        require_once __DIR__.'/app/AppKernel.php';

        return new AppKernel('test', true, isset($options['test_case']) ? $options['test_case'] : null);
    }

    protected static function createAuthenticatedClient($token = null)
    {
        if (null === static::$kernel) {
            static::bootKernel();
        }

        $client = static::$kernel->getContainer()->get('test.client');
        $token  = null === $token ? self::getAuthenticatedToken() : $token;

        if (null === $token) {
            throw new \LogicException('Unable to create an authenticated client from a null JWT token');
        }

        $client->setServerParameter('HTTP_AUTHORIZATION', sprintf('Bearer %s', $token));

        return $client;
    }

    protected static function getAuthenticatedToken()
    {
        $client = static::$client ?: static::$kernel->getContainer()->get('test.client');

        $client->request('POST', '/login_check', ['_username' => 'lexik', '_password' => 'dummy']);
        $responseBody = json_decode($client->getResponse()->getContent(), true);

        if (!isset($responseBody['token'])) {
            throw new \LogicException('Unable to get a JWT Token through the "/login_check" route.');
        }

        return $responseBody['token'];
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
