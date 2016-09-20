<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional;

use Symfony\Component\HttpFoundation\Response;

/**
 * Base class for classes testing the different cases of authentication via
 * JSON Web Token.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class CompleteTokenAuthenticationTest extends TestCase
{
    public static function setupBeforeClass()
    {
        static::bootKernel();
    }

    public function testAccessSecuredRoute()
    {
        static::$client = static::createAuthenticatedClient();
        static::accessSecuredRoute();

        $response = static::$client->getResponse();

        $this->assertSuccessful($response);

        return json_decode($response->getContent(), true);
    }

    public function testAccessSecuredRouteWithoutToken()
    {
        static::$client = static::createClient();
        static::accessSecuredRoute();

        $response = static::$client->getResponse();

        $this->assertFailure($response);

        return json_decode($response->getContent(), true);
    }

    public function testAccessSecuredRouteWithInvalidToken()
    {
        static::$client = static::createClient();
        static::$client->request('GET', '/api/secured', [], [], ['HTTP_AUTHORIZATION' => 'Bearer dummy']);

        $response = static::$client->getResponse();

        $this->assertFailure($response);

        return json_decode($response->getContent(), true);
    }

    /**
     * @group time-sensitive
     */
    public function testAccessSecuredRouteWithExpiredToken($fail = true)
    {
        static::bootKernel();

        $encoder = static::$kernel->getContainer()->get('lexik_jwt_authentication.encoder');
        $payload = ['exp' => time()];

        static::$client = static::createAuthenticatedClient($encoder->encode($payload));
        static::accessSecuredRoute();

        $response = static::$client->getResponse();

        if (true === $fail) {
            $this->assertFailure($response);
        }

        return json_decode($response->getContent(), true);
    }

    protected function assertFailure(Response $response)
    {
        $this->assertFalse($response->isSuccessful());
        $this->assertSame(401, $response->getStatusCode());
    }

    protected function assertSuccessful(Response $response)
    {
        $this->assertTrue($response->isSuccessful());
        $this->assertSame(200, $response->getStatusCode());
    }

    protected function accessSecuredRoute()
    {
        static::$client->request('GET', '/api/secured');
    }
}
