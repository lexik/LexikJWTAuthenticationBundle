<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional;

use Lcobucci\JWT\Parser;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;

class GetTokenTest extends TestCase
{
    public function testGetToken()
    {
        static::$client = static::createClient();
        static::$client->request('POST', '/login_check', ['_username' => 'lexik', '_password' => 'dummy']);

        $response = static::$client->getResponse();

        $this->assertInstanceOf(JWTAuthenticationSuccessResponse::class, $response);
        $this->assertTrue($response->isSuccessful());

        $body = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('token', $body, 'The response should have a "token" key containing a JWT Token.');
    }

    public function testGetTokenWithCustomClaim()
    {
        static::$client = static::createClient();

        $subscriber = static::$kernel->getContainer()->get('lexik_jwt_authentication.test.jwt_event_subscriber');
        $subscriber->setListener(Events::JWT_CREATED, function (JWTCreatedEvent $e) {
            $e->setData($e->getData() + ['custom' => 'dummy']);
            $e->setHeader($e->getHeader() + ['foo' => 'bar']);
        });

        static::$client->request('POST', '/login_check', ['_username' => 'lexik', '_password' => 'dummy']);

        $body    = json_decode(static::$client->getResponse()->getContent(), true);
        $decoder = static::$kernel->getContainer()->get('lexik_jwt_authentication.encoder');
        $payload = $decoder->decode($body['token']);

        $this->assertArrayHasKey('custom', $payload, 'The payload should contains a "custom" claim.');
        $this->assertSame('dummy', $payload['custom'], 'The "custom" claim should be equal to "dummy".');

        $jws = (new Parser())->parse((string) $body['token']);
        $this->assertArrayHasKey('foo', $jws->getHeaders(), 'The payload should contains a custom "foo" header.');
    }

    public function testGetTokenFromInvalidCredentials()
    {
        static::$client = static::createClient();
        static::$client->request('POST', '/login_check', ['_username' => 'lexik', '_password' => 'wrong']);

        $response = static::$client->getResponse();

        $body = json_decode($response->getContent(), true);

        $this->assertFalse($response->isSuccessful());
        $this->assertSame(401, $response->getStatusCode());

        $this->assertArrayHasKey('message', $body, 'The response should have a "message" key containing the failure reason.');
        $this->assertArrayHasKey('code', $body, 'The response should have a "code" key containing the response status code.');

        $this->assertSame('Invalid credentials.', $body['message']);
        $this->assertSame(401, $body['code']);
    }
}
