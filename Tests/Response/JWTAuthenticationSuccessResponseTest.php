<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Response;

use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;

/**
 * Tests the JWTAuthenticationSuccessResponse.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
final class JWTAuthenticationSuccessResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testResponse()
    {
        $extraData = [
            'username' => 'foobar',
            'email'    => 'dev@lexik.fr'
        ];
        $expected = ['token' => 'jwt'] + $extraData;

        $response = new JWTAuthenticationSuccessResponse($expected['token'], $extraData);

        $this->assertSame($expected['token'], $response->getToken());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($extraData, $response->getExtraData());

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
        $this->assertNotEquals(json_encode($replacementData), $response->getContent());
        $this->assertSame(
            json_encode(['token' => $response->getToken()] + $response->getExtraData()),
            $response->getContent()
        );
    }
}
