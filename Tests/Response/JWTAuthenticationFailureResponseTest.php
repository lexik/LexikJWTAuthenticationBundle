<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Response;

use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use PHPUnit\Framework\TestCase;

/**
 * Tests the JWTAuthenticationFailureResponse.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
final class JWTAuthenticationFailureResponseTest extends TestCase
{
    public function testResponse()
    {
        $expected = [
            'code'    => 401,
            'message' => 'message',
        ];

        $response = new JWTAuthenticationFailureResponse($expected['message']);

        $this->assertSame($expected['message'], $response->getMessage());
        $this->assertSame($expected['code'], $response->getStatusCode());
        $this->assertSame('Bearer', $response->headers->get('WWW-Authenticate'));
        $this->assertSame(json_encode($expected), $response->getContent());

        return $response;
    }

    /**
     * @depends testResponse
     */
    public function testSetMessage(JWTAuthenticationFailureResponse $response)
    {
        $newMessage = 'new message';
        $response->setMessage($newMessage);

        $responseBody = json_decode($response->getContent());

        $this->assertSame($response->getStatusCode(), $responseBody->code);
        $this->assertSame($newMessage, $response->getMessage());
        $this->assertSame($newMessage, $responseBody->message);
    }
}
