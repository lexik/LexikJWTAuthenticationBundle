<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * TestCase.
 */
abstract class TestCase extends WebTestCase
{
    use ForwardCompatTestCaseTrait;

    protected static $client;

    /**
     * {@inheritdoc}
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        require_once __DIR__ . '/app/AppKernel.php';

        return new AppKernel('test', true, $options['test_case'] ?? null);
    }

    protected static function createAuthenticatedClient($token = null)
    {
        $client = static::$client ?: static::createClient();
        $token = $token ?? self::getAuthenticatedToken();

        if (null === $token) {
            throw new \LogicException('Unable to create an authenticated client from a null JWT token');
        }

        $client->setServerParameter('HTTP_AUTHORIZATION', sprintf('Bearer %s', $token));

        return $client;
    }

    protected static function getAuthenticatedToken(string $username = 'lexik')
    {
        $client = static::$client ?: static::createClient();

        $client->jsonRequest('POST', '/login_check', ['username' => $username, 'password' => 'dummy']);
        $response = $client->getResponse();
        $responseBody = json_decode($response->getContent(), true);

        if (!isset($responseBody['token'])) {
            $cookies = $response->headers->getCookies();
            if (isset($cookies[0]) && 'token' === $cookies[0]->getName()) {
                return $cookies[0]->getValue();
            }

            throw new \LogicException('Unable to get a JWT Token through the "/login_check" route.');
        }

        return $responseBody['token'];
    }

    /**
     * {@inheritdoc}
     */
    protected function doSetUp()
    {
        $fs = new Filesystem();
        $fs->remove(sys_get_temp_dir() . '/LexikJWTAuthenticationBundle/');
    }
}
