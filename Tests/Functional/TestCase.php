<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional;

use LogicException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * TestCase.
 */
abstract class TestCase extends WebTestCase
{
    protected static $client;

    protected static function createAuthenticatedClient($token = null)
    {
        $client = static::$client ?: static::createClient();
        $token ??= self::getAuthenticatedToken();

        if (null === $token) {
            throw new LogicException('Unable to create an authenticated client from a null JWT token');
        }

        $client->setServerParameter('HTTP_AUTHORIZATION', sprintf('Bearer %s', $token));

        return $client;
    }

    protected static function getAuthenticatedToken()
    {
        $client = static::$client ?: static::createClient();

        $client->jsonRequest('POST', '/login_check', ['username' => 'lexik', 'password' => 'dummy']);
        $response = $client->getResponse();
        $responseBody = json_decode($response->getContent(), true);

        if (!isset($responseBody['token'])) {
            $cookies = $response->headers->getCookies();
            if (isset($cookies[0]) && 'token' === $cookies[0]->getName()) {
                return $cookies[0]->getValue();
            }

            throw new LogicException('Unable to get a JWT Token through the "/login_check" route.');
        }

        return $responseBody['token'];
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $fs = new Filesystem();
        $fs->remove(sys_get_temp_dir() . '/LexikJWTAuthenticationBundle/');
    }

    protected function tearDown(): void
    {
        static::ensureKernelShutdown();
        static::$kernel = null;
    }
}
