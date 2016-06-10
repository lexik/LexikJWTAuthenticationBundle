<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Signature;

use Lexik\Bundle\JWTAuthenticationBundle\Signature\CreatedJWS;

/**
 * Tests the CreatedJWS model class.
 */
final class CreatedJWSTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateUnsigned()
    {
        $jws = new CreatedJWS($token = 'jwt', false);

        $this->assertSame($token, $jws->getToken());
        $this->assertFalse($jws->isSigned());
    }

    public function testCreateSigned()
    {
        $jws = new CreatedJWS($token = 'jwt', true);

        $this->assertSame($token, $jws->getToken());
        $this->assertTrue($jws->isSigned());
    }
}
