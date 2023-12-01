<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A128GCM;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A256GCM;
use Jose\Component\Encryption\Algorithm\KeyEncryption\A128GCMKW;
use Jose\Component\Encryption\Algorithm\KeyEncryption\A256GCMKW;
use Jose\Component\Encryption\Compression\CompressionMethodManager;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Encryption\Serializer\CompactSerializer as JweCompactSerializer;
use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\Algorithm\HS512;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer as JwsCompactSerializer;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base class for classes testing the different cases of authentication via
 * JSON Web Token.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 * @group web-token
 */
class WebTokenTest extends TestCase
{
    public function testAnEncryptedAccessTokenCanBeComputed()
    {
        //Given
        static::$client = static::createClient(['test_case' => 'WebToken']);

        //When
        static::$client->jsonRequest('POST', '/login_check', ['username' => 'lexik', 'password' => 'dummy']);
        $response = static::$client->getResponse();

        //Then
        $this->assertInstanceOf(JWTAuthenticationSuccessResponse::class, $response);
        $this->assertTrue($response->isSuccessful());

        $token = $this->getTokenFromResponse($response);
        $split = explode('.', $token);
        // This is a JWE
        static::assertCount(5, $split);

        $encodedProtectedHeader = $split[0];
        $protectedHeader = json_decode(Base64UrlSafe::decodeNoPadding($encodedProtectedHeader), true);
        static::assertArrayHasKey('crit', $protectedHeader);
        // The event is caught and the critical header parameters are set
        static::assertSame(['exp', 'iat', 'nbf'], $protectedHeader['crit']);
    }

    public function testAnAccessTokenCanBeUsedForApiCall()
    {
        //Given
        static::$client = static::createClient(['test_case' => 'WebToken']);
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

        //When
        static::$client->jsonRequest('POST', '/login_check', ['username' => 'lexik', 'password' => 'dummy']);
        static::$client->request('GET', '/api/secured', [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->getTokenFromResponse(static::$client->getResponse())]);
        static::$client->getResponse();

        //Then
        static::assertArrayHasKey('added_data', $payloadTested->payload, 'The payload should contains a "added_data" claim.');
        static::assertSame('still visible after the event', $payloadTested->payload['added_data'], 'The "added_data" claim should be equal to "still visible after the event".');
    }

    /**
     * @dataProvider getInvalidTokens
     */
    public function testInvalidToken(string $token, string $message)
    {
        //Given
        static::$client = static::createClient(['test_case' => 'WebToken']);
        $messageTested = new \stdClass();
        $messageTested->messages = [];
        $subscriber = static::$kernel->getContainer()->get('lexik_jwt_authentication.test.jwt_event_subscriber');
        $subscriber->setListener(Events::JWT_INVALID, function (JWTInvalidEvent $event) use ($messageTested) {
            $e = $event->getException();
            while ($e !== null) {
                $messageTested->messages[] = $e->getMessage();
                $e = $e->getPrevious();
            }
        });

        //When
        static::$client->request('GET', '/api/secured', [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]);
        $response = static::$client->getResponse();

        //Then
        static::assertContains($message, $messageTested->messages, sprintf('Messages: %s', implode($messageTested->messages)));
        static::assertResponseStatusCodeSame(401, $response);
    }

    public function testCriticalHeaderNotVerified()
    {
        static::$client = static::createClient(['test_case' => 'WebToken']);
        $subscriber = static::$kernel->getContainer()->get('lexik_jwt_authentication.test.jwt_event_subscriber');
        $messageTested = new \stdClass();
        $messageTested->messages = [];
        $subscriber->setListener(Events::JWT_INVALID, function (JWTInvalidEvent $event) use ($messageTested) {
            $e = $event->getException();
            while ($e !== null) {
                $messageTested->messages[] = $e->getMessage();
                $e = $e->getPrevious();
            }
        });
        $time = time();
        $token = $this->buildJWS(
            ["jti" => "62b9d7514d43b7.68236706", "iat" => $time - 1, "nbf" => $time - 1, "exp" => $time + 3600, "roles" => ["ROLE_USER"], "username" => "lexik"],
            ['alg' => 'HS256'],
            $this->getSignatureKey()
        );
        $token = $this->buildJWE(
            $token,
            ["cty" => "JWT", "typ" => "JWT", 'alg' => 'A256GCMKW', 'enc' => 'A256GCM', "iat" => $time - 1, "nbf" => $time - 1, "exp" => $time + 3600, 'crit' => ['exp', 'iat', 'nbf', 'foo']],
            $this->getEncryptionKey()
        );

        //When
        static::$client->request('GET', '/api/secured', [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]);
        $response = static::$client->getResponse();

        //Then
        static::assertContains('Unable to load and decrypt the token.', $messageTested->messages, sprintf('Messages: %s', implode($messageTested->messages)));
        static::assertResponseStatusCodeSame(401, $response);
    }

    public function getInvalidTokens(): iterable
    {
        // Not encrypted token
        yield [
            $this->buildJWS(['username' => 'foo'], ['alg' => 'HS256'], $this->getSignatureKey()),
            "Invalid token. The token cannot be decrypted.",
        ];

        $validJws = $this->buildJWS(
            ["jti" => "62b9d7514d43b7.68236706", "iat" => time() - 1, "nbf" => time() - 1, "exp" => time() + 3600, "roles" => ["ROLE_USER"], "username" => "lexik"],
            ['alg' => 'HS256'],
            $this->getSignatureKey()
        );
        // Unsupported key encryption algorithm "A128GCMKW"
        yield [
            $this->buildJWE(
                $validJws,
                ["cty" => "JWT", "typ" => "JWT", 'alg' => 'A128GCMKW', 'enc' => 'A256GCM'],
                $this->getEncryptionKey()
            ),
            "Invalid token. The token cannot be decrypted.",
        ];
        // Unsupported content encryption algorithm "A128GCM"
        yield [
            $this->buildJWE(
                $validJws,
                ["cty" => "JWT", "typ" => "JWT", 'alg' => 'A256GCMKW', 'enc' => 'A128GCM'],
                $this->getEncryptionKey()
            ),
            "Invalid token. The token cannot be decrypted.",
        ];
        // Unknown key
        yield [
            $this->buildJWE(
                $validJws,
                ["cty" => "JWT", "typ" => "JWT", 'alg' => 'A256GCMKW', 'enc' => 'A256GCM'],
                $this->getOtherEncryptionKey()
            ),
            "Invalid token. The token cannot be decrypted.",
        ];
        // Bad content
        yield [
            $this->buildJWE(
                'arbitrary data',
                ["cty" => "JWT", "typ" => "JWT", 'alg' => 'A256GCMKW', 'enc' => 'A256GCM'],
                $this->getEncryptionKey()
            ),
            "Invalid token. The token cannot be loaded or the signature cannot be verified.",
        ];

        [$header, $payload] = explode('.', $validJws);
        $jwsWithInvalidSignature = sprintf('%s.%s.%s', $header, $payload, Base64UrlSafe::encodeUnpadded(hash('sha3-256', 'fooBAR')));
        // Invalid signature
        yield [
            $this->buildJWE(
                $jwsWithInvalidSignature,
                ["cty" => "JWT", "typ" => "JWT", 'alg' => 'A256GCMKW', 'enc' => 'A256GCM'],
                $this->getEncryptionKey()
            ),
            "Invalid token. The token cannot be loaded or the signature cannot be verified.",
        ];

        $jwsWithInvalidAlgorithm = $this->buildJWS(
            ["jti" => "62b9d7514d43b7.68236706", "iat" => time() - 1, "nbf" => time() - 1, "exp" => time() + 3600, "roles" => ["ROLE_USER"], "username" => "lexik"],
            ['alg' => 'HS512'],
            $this->getSignatureKey()
        );
        // Unsupported algorithm
        yield [
            $this->buildJWE(
                $jwsWithInvalidAlgorithm,
                ["cty" => "JWT", "typ" => "JWT", 'alg' => 'A256GCMKW', 'enc' => 'A256GCM'],
                $this->getEncryptionKey()
            ),
            "Invalid token. The token cannot be loaded or the signature cannot be verified.",
        ];

        $jwsSignedWithOtherKey = $this->buildJWS(
            ["jti" => "62b9d7514d43b7.68236706", "iat" => time() - 1, "nbf" => time() - 1, "exp" => time() + 3600, "roles" => ["ROLE_USER"], "username" => "lexik"],
            ['alg' => 'HS256'],
            $this->getOtherSignatureKey()
        );
        // Missing key
        yield [
            $this->buildJWE(
                $jwsSignedWithOtherKey,
                ["cty" => "JWT", "typ" => "JWT", 'alg' => 'A256GCMKW', 'enc' => 'A256GCM'],
                $this->getEncryptionKey()
            ),
            "Invalid token. The token cannot be loaded or the signature cannot be verified.",
        ];

        $expiredJws = $this->buildJWS(
            ["jti" => "62b9d7514d43b7.68236706", "iat" => time() - 1, "nbf" => time() - 1, "exp" => time() - 1, "roles" => ["ROLE_USER"], "username" => "lexik"],
            ['alg' => 'HS256'],
            $this->getSignatureKey()
        );
        // Expired token
        yield [
            $this->buildJWE(
                $expiredJws,
                ["cty" => "JWT", "typ" => "JWT", 'alg' => 'A256GCMKW', 'enc' => 'A256GCM'],
                $this->getEncryptionKey()
            ),
            "The token expired.",
        ];

        $notYetJws = $this->buildJWS(
            ["jti" => "62b9d7514d43b7.68236706", "iat" => time() - 1, "nbf" => time() + 1800, "exp" => time() + 3600, "roles" => ["ROLE_USER"], "username" => "lexik"],
            ['alg' => 'HS256'],
            $this->getSignatureKey()
        );
        // nbf claim cannot be verified
        yield [
            $this->buildJWE(
                $notYetJws,
                ["cty" => "JWT", "typ" => "JWT", 'alg' => 'A256GCMKW', 'enc' => 'A256GCM'],
                $this->getEncryptionKey()
            ),
            "The JWT can not be used yet.",
        ];

        $notBeforeJws = $this->buildJWS(
            ["jti" => "62b9d7514d43b7.68236706", "iat" => time() + 1800, "nbf" => time() - 1, "exp" => time() + 3600, "roles" => ["ROLE_USER"], "username" => "lexik"],
            ['alg' => 'HS256'],
            $this->getSignatureKey()
        );
        // iat claim cannot be verified
        yield [
            $this->buildJWE(
                $notBeforeJws,
                ["cty" => "JWT", "typ" => "JWT", 'alg' => 'A256GCMKW', 'enc' => 'A256GCM'],
                $this->getEncryptionKey()
            ),
            "The JWT is issued in the future.",
        ];

        $jwsWithoutMandatoryClaims = $this->buildJWS(
            ["jti" => "62b9d7514d43b7.68236706", "roles" => ["ROLE_USER"], "username" => "lexik"],
            ['alg' => 'HS256'],
            $this->getSignatureKey()
        );
        // Missing mandatory claims
        yield [
            $this->buildJWE(
                $jwsWithoutMandatoryClaims,
                ["cty" => "JWT", "typ" => "JWT", 'alg' => 'A256GCMKW', 'enc' => 'A256GCM'],
                $this->getEncryptionKey()
            ),
            "The following claims are mandatory: exp, iat, nbf.",
        ];
    }

    /**
     * @return string
     */
    private function getTokenFromResponse(Response $response)
    {
        if (Response::HTTP_NO_CONTENT === $response->getStatusCode()) {
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

    private function buildJWS(array $claims, array $header, JWK $signatureKey): string
    {
        $builder = new JWSBuilder(new AlgorithmManager([new HS256(), new HS512()]));
        $jws = $builder
            ->create()
            ->withPayload(json_encode($claims, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            ->addSignature($signatureKey, $header)
            ->build()
        ;

        return (new JwsCompactSerializer())->serialize($jws);
    }

    private function buildJWE(string $payload, array $header, JWK $encryptionKey): string
    {
        $builder = new JWEBuilder(
            new AlgorithmManager([new A256GCMKW(), new A128GCMKW()]),
            new AlgorithmManager([new A256GCM(), new A128GCM()]),
            new CompressionMethodManager([])
        );
        $jwe = $builder
            ->create()
            ->withPayload($payload)
            ->withSharedProtectedHeader($header)
            ->addRecipient($encryptionKey)
            ->build()
        ;

        return (new JweCompactSerializer())->serialize($jwe);
    }

    private function getSignatureKey(): JWK
    {
        return JWK::createFromJson('{"kty":"oct","k":"ydYSJsYZAG_eCiher9k4C2fODuYAZ5beELzMgEQ-ErLTb5yUfBaWm1AKbY6RS4cH6nmhxnXAhjSPsClghamYtg"}');
    }

    private function getEncryptionKey(): JWK
    {
        return JWK::createFromJson('{"kty":"oct","k":"eKSyjm9jIbCfpiNE2B9KK9Xug7ksCSc_Gqn4-1P4DSkjcSwj72kPhZuijI-mrsPOaXN7yppDnUr6g6wenwg19w"}');
    }

    private function getOtherEncryptionKey(): JWK
    {
        return JWK::createFromJson('{"kty":"oct","k":"7G358rSZ1sKBVCEaqIKFqbsg_WFgIw4bsrh2JIOkyIHD1su5A8OYlLKwnCyoDCi6fLMpwuomSbVjBsVPBKs3sQ"}');
    }

    private function getOtherSignatureKey(): JWK
    {
        return JWK::createFromJson('{"kty":"oct","k":"-osKVtVvC5syXMbH6dcKfNW9Vxy3NgthzCwR8oPmsjET1yS6qjHtVKkeuLPc8aHzr4OjZL_PFZOigKr38pUoIw"}');
    }
}
