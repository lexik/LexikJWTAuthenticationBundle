<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Response;

use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;

/**
 * Tests the JWTAuthenticationFailureResponse
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
final class JWTAuthenticationFailureResponseTest extends \PHPUnit_Framework_TestCase
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
}
