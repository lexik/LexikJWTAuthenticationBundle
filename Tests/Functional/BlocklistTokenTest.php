<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional;

use Lexik\Bundle\JWTAuthenticationBundle\Services\CacheItemPoolBlockedTokenManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\Tests\Stubs\UserProvider;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Response;

class BlocklistTokenTest extends TestCase
{
    public function testShouldInvalidateTokenOnLogoutWhenBlockListTokenIsEnabled()
    {
        static::$client = static::createClient(['test_case' => 'BlockListToken']);

        $token = static::getAuthenticatedToken();

        static::$client->jsonRequest('GET', '/api/secured', [], ['HTTP_AUTHORIZATION' => "Bearer $token"]);
        static::assertResponseIsSuccessful('Precondition - a valid token should be able to contact the api');

        static::$client->jsonRequest('GET', '/api/logout', [], ['HTTP_AUTHORIZATION' => "Bearer $token"]);
        static::assertResponseStatusCodeSame(Response::HTTP_FOUND);

        static::$client->jsonRequest('GET', '/api/secured', [], ['HTTP_AUTHORIZATION' => "Bearer $token"]);
        static::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED, 'Logout should invalidate token');

        $responseBody = json_decode(static::$client->getResponse()->getContent(), true);
        $this->assertEquals('Invalid JWT Token', $responseBody['message']);
        $this->assertThatTokenIsInTheBlockList($token);
    }

    public function testShouldAddJtiWhenBlockListTokenIsEnabled()
    {
        static::$client = static::createClient(['test_case' => 'BlockListToken']);

        $token = static::getAuthenticatedToken();
        /** @var JWTManager $jwtManager */
        $jwtManager = static::getContainer()->get('lexik_jwt_authentication.jwt_manager');
        $payload = $jwtManager->parse($token);
        self::assertNotEmpty($payload['jti']);
    }

    public function testShouldInvalidateTokenOnLogoutWhenBlockListTokenIsEnabledAndWhenUsingCustomLogout()
    {
        static::$client = static::createClient(['test_case' => 'BlockListToken']);

        $token = static::getAuthenticatedToken();

        static::$client->jsonRequest('GET', '/api/secured', [], ['HTTP_AUTHORIZATION' => "Bearer $token"]);
        static::assertResponseIsSuccessful('Precondition - a valid token should be able to contact the api');

        static::$client->jsonRequest('GET', '/api/logout_custom', [], ['HTTP_AUTHORIZATION' => "Bearer $token"]);
        static::assertResponseStatusCodeSame(Response::HTTP_OK);

        static::$client->jsonRequest('GET', '/api/secured', [], ['HTTP_AUTHORIZATION' => "Bearer $token"]);
        static::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED, 'Logout should invalidate token');

        $responseBody = json_decode(static::$client->getResponse()->getContent(), true);
        $this->assertEquals('Invalid JWT Token', $responseBody['message']);
        $this->assertThatTokenIsInTheBlockList($token);
    }

    public function testShouldNotInvalidateTokenOnLogoutWhenBlockListTokenIsDisabled()
    {
        static::$client = static::createClient(['test_case' => 'BlockListTokenDisabled']);
        $token = static::getAuthenticatedToken();

        static::$client->jsonRequest('GET', '/api/secured', [], ['HTTP_AUTHORIZATION' => "Bearer $token"]);
        static::assertResponseIsSuccessful('Precondition - a valid token should be able to contact the api');

        static::$client->jsonRequest('GET', '/api/logout', [], ['HTTP_AUTHORIZATION' => "Bearer $token"]);
        static::assertResponseStatusCodeSame(Response::HTTP_FOUND);

        static::$client->jsonRequest('GET', '/api/secured', [], ['HTTP_AUTHORIZATION' => "Bearer $token"]);
        static::assertResponseStatusCodeSame(Response::HTTP_OK, 'Logout should NOT invalidate token when block list config is not enabled');
    }

    public function testShouldNotAddJtiWhenBlockListTokenIsDisabled()
    {
        static::$client = static::createClient(['test_case' => 'BlockListTokenDisabled']);

        $token = static::getAuthenticatedToken();
        /** @var JWTManager $jwtManager */
        $jwtManager = static::getContainer()->get('lexik_jwt_authentication.jwt_manager');
        $payload = $jwtManager->parse($token);
        self::assertArrayNotHasKey('jti', $payload);
    }

    public function testShouldInvalidateTokenIfDisabledUserWhenBlockListTokenIsEnabled()
    {
        static::$client = static::createClient(['test_case' => 'BlockListToken']);
        if ('lexik_jwt' === static::$kernel->getUserProvider()) {
            $this->markTestSkipped('Test not implemented with lexik_jwt provider');
        }

        UserProvider::$users['lexik_disabled']['enabled'] = true;
        $token = static::getAuthenticatedToken('lexik_disabled');

        UserProvider::$users['lexik_disabled']['enabled'] = true;
        static::$client->jsonRequest('GET', '/api/secured', [], ['HTTP_AUTHORIZATION' => "Bearer $token"]);
        static::assertResponseIsSuccessful('Should be able to contact the api');

        /** @var UserProvider $userProvider */
        UserProvider::$users['lexik_disabled']['enabled'] = false;
        static::$client->jsonRequest('GET', '/api/secured', [], ['HTTP_AUTHORIZATION' => "Bearer $token"]);
        static::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED, 'An user disabled should not be able to contact the api');
        $this->assertThatTokenIsInTheBlockList($token);
    }

    private function assertThatTokenIsInTheBlockList(string $token): void
    {
        /** @var JWTManager $jwtManager */
        $jwtManager = static::getContainer()->get('lexik_jwt_authentication.jwt_manager');
        $payload = $jwtManager->parse($token);

        /** @var CacheItemPoolInterface $cache */
        $cache = static::getContainer()->get('lexik_jwt_authentication.blocklist_token.cache');
        self::assertTrue($cache->hasItem($payload['jti']), 'The token should be in the block list');
    }
}
