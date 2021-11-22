<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Security\Http\Cookie;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Cookie\JWTCookieProvider;
use PHPUnit\Framework\TestCase;

/**
 * JWTCookieProviderTest.
 */
class JWTCookieProviderTest extends TestCase
{
    public function testCreateCookieWithExpiration()
    {
        $expiresAt = time() + 3600;
        $cookieProvider = new JWTCookieProvider("default_name");
        $cookie = $cookieProvider->createCookie("header.payload.signature", "name", $expiresAt);

        $this->assertEquals($expiresAt, $cookie->getExpiresTime());
    }

    public function testCreateCookieWithLifetime()
    {
        $lifetime = 3600;
        $cookieProvider = new JWTCookieProvider("default_name", $lifetime);
        $cookie = $cookieProvider->createCookie("header.payload.signature");

        $this->assertEquals(time() + $lifetime, $cookie->getExpiresTime());
    }

    public function testCreateSessionCookie()
    {
        $cookieProvider = new JWTCookieProvider("default_name", 0);
        $cookie = $cookieProvider->createCookie("header.payload.signature");

        $this->assertEquals(0, $cookie->getExpiresTime());
    }
}
