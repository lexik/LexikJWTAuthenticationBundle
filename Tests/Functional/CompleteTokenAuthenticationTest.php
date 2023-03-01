<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional;

use Lexik\Bundle\JWTAuthenticationBundle\Tests\Stubs\JWTUser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\User;

/**
 * Base class for classes testing the different cases of authentication via
 * JSON Web Token.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class CompleteTokenAuthenticationTest extends TestCase
{
    protected function doSetUp()
    {
        parent::doSetUp();

        static::$client = static::createClient();
    }

    public function testAccessSecuredRoute()
    {
        static::$client = static::createAuthenticatedClient();
        static::accessSecuredRoute();

        static::assertResponseIsSuccessful();
        static::assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = static::$client->getResponse();
        $content = json_decode($response->getContent(), true);

        $this->assertSuccessful($response);

        if ('lexik_jwt' === static::$kernel->getUserProvider()) {
            $this->assertSame(JWTUser::class, $content['class']);
        } elseif (class_exists(InMemoryUser::class)) {
            $this->assertSame(InMemoryUser::class, $content['class']);
        } else {
            $this->assertSame(User::class, $content['class']);
        }

        return $content;
    }

    public function testAccessSecuredRouteWithoutToken()
    {
        static::accessSecuredRoute();

        $response = static::$client->getResponse();

        $this->assertFailure($response);

        return json_decode($response->getContent(), true);
    }

    public function testAccessSecuredRouteWithInvalidToken($token = 'dummy')
    {
        static::$client->jsonRequest('GET', '/api/secured', [], ['HTTP_AUTHORIZATION' => "Bearer $token"]);

        $response = static::$client->getResponse();

        $this->assertFailure($response);

        return json_decode($response->getContent(), true);
    }

    public function testAccessSecuredRouteWithInvalidButCorrectlyFormattedToken()
    {
        return $this->testAccessSecuredRouteWithInvalidToken('a.a.a');
    }

    /**
     * @group time-sensitive
     */
    public function testAccessSecuredRouteWithExpiredToken($fail = true)
    {
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

    public function testExpClaimIsNotSetIfNoTTL()
    {
        $encoder = static::$kernel->getContainer()->get('lexik_jwt_authentication.encoder');
        $idClaim = static::$kernel->getContainer()->getParameter('lexik_jwt_authentication.user_id_claim');

        $r = new \ReflectionProperty(get_class($encoder), 'jwsProvider');
        $r->setAccessible(true);
        $jwsProvider = $r->getValue($encoder);
        \Closure::bind(function () {
            $this->ttl = null;
            $this->allowNoExpiration = true;
        }, $jwsProvider, get_class($jwsProvider))->__invoke();

        $token = $encoder->encode([$idClaim => 'lexik']);
        $this->assertArrayNotHasKey('exp', $encoder->decode($token));

        static::$client = static::createAuthenticatedClient($token);
        static::accessSecuredRoute();

        $this->assertSuccessful(static::$client->getResponse());
    }

    protected function assertFailure(Response $response)
    {
        $this->assertFalse($response->isSuccessful());
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    protected function assertSuccessful(Response $response)
    {
        $this->assertTrue($response->isSuccessful());
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    protected function accessSecuredRoute()
    {
        static::$client->jsonRequest('GET', '/api/secured');
    }
}
