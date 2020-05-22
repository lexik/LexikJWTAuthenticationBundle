<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional;

use Lcobucci\JWT\Parser;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class GetTokenTest extends TestCase
{
    public function testGetToken()
    {
        static::$client = static::createClient();
        static::$client->request('POST', '/login_check', ['_username' => 'lexik', '_password' => 'dummy']);

        $response = static::$client->getResponse();

        $this->assertInstanceOf(JWTAuthenticationSuccessResponse::class, $response);
        $this->assertTrue($response->isSuccessful());

        $this->getToken($response);
    }

    public function testGetTokenWithListener()
    {
        static::$client = static::createClient();

        $subscriber = static::$kernel->getContainer()->get('lexik_jwt_authentication.test.jwt_event_subscriber');
        $subscriber->setListener(Events::JWT_DECODED, function (JWTDecodedEvent $e) {
            $payload = $e->getPayload();
            $payload['added_data'] = 'still visible after the event';
            $e->setPayload($payload);
        });

        $payloadTested = new \stdClass();
        $payloadTested->payload = [];
        $subscriber->setListener(Events::JWT_AUTHENTICATED, function (JWTAuthenticatedEvent $e) use ($payloadTested) {
            $payloadTested->payload = $e->getPayload();
        });

        static::$client->request('POST', '/login_check', ['_username' => 'lexik', '_password' => 'dummy']);
        static::$client->request('GET', '/api/secured', [], [], [ 'HTTP_AUTHORIZATION' => "Bearer ".$this->getToken(static::$client->getResponse()) ]);

        $this->assertArrayHasKey('added_data', $payloadTested->payload, 'The payload should contains a "added_data" claim.');
        $this->assertSame('still visible after the event', $payloadTested->payload['added_data'], 'The "added_data" claim should be equal to "still visible after the event".');
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

        $decoder = static::$kernel->getContainer()->get('lexik_jwt_authentication.encoder');
        $payload = $decoder->decode($token = $this->getToken(static::$client->getResponse()));

        $this->assertArrayHasKey('custom', $payload, 'The payload should contains a "custom" claim.');
        $this->assertSame('dummy', $payload['custom'], 'The "custom" claim should be equal to "dummy".');

        $jws = (new Parser())->parse($token);
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

    private function getToken(Response $response)
    {
        if (204 === $response->getStatusCode()) {
            $cookies = $response->headers->getCookies();
            if (isset($cookies[0]) && 'token' === $cookies[0]->getName()) {
                $this->assertSame(Cookie::SAMESITE_STRICT, $cookies[0]->getSameSite());
                return $cookies[0]->getValue();
            }

            $this->fail('No token found in response.');
        }

        $body = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $body, 'The response should have a "token" key containing a JWT Token.');

        return $body['token'];
    }
}
