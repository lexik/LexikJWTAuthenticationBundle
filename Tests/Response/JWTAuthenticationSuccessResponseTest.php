<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Response;

use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests the JWTAuthenticationSuccessResponse.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
final class JWTAuthenticationSuccessResponseTest extends TestCase
{
    public function testResponse()
    {
        $data = [
            'username' => 'foobar',
            'email' => 'dev@lexik.fr',
        ];
        $expected = ['token' => 'jwt'] + $data;
        $response = new JWTAuthenticationSuccessResponse($expected['token'], $data);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(json_encode($expected), $response->getContent());

        return $response;
    }

    /**
     * @depends testResponse
     */
    public function testReplaceData(JWTAuthenticationSuccessResponse $response)
    {
        $replacementData = ['foo' => 'bar'];
        $response->setData($replacementData);

        // Test that the previous method call has no effect on the original body
        $this->assertEquals(json_encode($replacementData), $response->getContent());
        $this->assertObjectNotHasAttribute('token', json_decode($response->getContent(), null));
    }
}
