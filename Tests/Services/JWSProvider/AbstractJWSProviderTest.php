<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Services\JWSProvider;

use Lexik\Bundle\JWTAuthenticationBundle\Signature\CreatedJWS;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\LoadedJWS;
use PHPUnit\Framework\TestCase;

/**
 * Tests the JWSProvider.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
abstract class AbstractJWSProviderTest extends TestCase
{
    protected static $privateKey = '
-----BEGIN RSA PRIVATE KEY-----
Proc-Type: 4,ENCRYPTED
DEK-Info: AES-256-CBC,BBE45AC4E18DAF41A11D58B6C9271E3E

XJYxO0PVMVwxv7vJR6DHqAWnPtdIH9LKWG7zUGjBM7mZ1GqcGsrqnKOnJh2IbOoX
/M05QarSt3JjcOb9FFElMF5M8oq9PieuyT9e4MG5HiIEYKm5aOvjJdjkZUS8H3RZ
e5CHPCxRSkYFsWlcnsgHZotptoekiURdsiUM9efUI2USnu2FUK3SO4AuMDSkwMFg
hS74KDS1Inoxfk5nj00fxJlR81e1AGs/C334FGqIf1my/WI+n9DlnQJCmmMc/W5N
O2ja/SACJbhVXoVSUvEgNg+BOMsa9G+fhP2bDCeNnhJLOJxiTOvZ55rc9ggTxgBt
FI/Yf7U+V9GKwsH1IU+w14m4c1LX5DmBqO1vDbPIdQL4+ZLe0+gqF4n4AiGO+01a
4KieGKGv0Bx2hTxwNG+IERw1pEUyUh7Ou8zfXgHRX7b5D9Qg6D5gHRvJuhAA8PeP
80w8ZDhk9M+Ypd50p/kSfbThrkjC7ZEBzXfzR1/G1a6Iqg1cx/Jm3W64zHiMRBvF
YXN4o4DnPKGcp6YaCwreHW3BcVbu/EQlEWPn43nxFTWDpSB094cf6EY+EwrwGaCf
rPHeyTXcuBqVxSRhjrQ7fZUib+ZSfXPToqGC1yPlmj1QryHkF2QoYwUFAHQnVVnl
Hmwih6bwh++96aVi0hquqj0wudPgq5lOQBhK3SqCqMgfvqVMu2lwUpOrxJ1GkJy8
Vb7gE+IAqDwOXGqyvrwp6cg5eIZeBT81a32L+bvKGcyshMK5/qD5j/xo8+UsICZW
XImgkbED6dKSw//vT2jr81PJrDuwKgBVOkDGpx3ZRhtZge478OYMLIFGlVcRUbaG
LoS0kFjAjMFjXbs9+UKAdk1DGM9kaqC1+HPFg7hKn969LKiwPS8lqtKAyeOASDwu
oq/sKeGWqMCzQUYdbzPCUW7/bQbvfc8BZy7cO8LyDtpsCSRwHd9S33w6IEdBxcTk
UnrzeL1qaS76f6/AxhRZAWsa/Z4dx1OU86Ht6hJVdzIxTTrZ1wyi0eAA20GvJOIc
C7NOwWzztyz9pmIctlB0zsbEYmNSInxCqufXfKbeDkfpJ5HCMhxTFnM7KfULXJ7y
VRssJoJoBbug2g5Q/j2k715tQmjXk9tHBILklUxnk85FcNO70NH6HZGxdJWaykPe
E1sGnTSN7nvachem5nfjW+u3N1YYAzrlX9Tj1GgEXX7TuXSEBMalh4CWVdoegwN8
owBvIsNGPfWxWyarDQ8vbn2Qwh4n3ajDp17Yy9Vk98odmQqQTdqpAuLG/pqmiFgr
rzs815yTuOWM5cwgHHOUwJgR6wXsRS6I132G+rdQe+CGszYcpR4fS9ZCc9iYYs3H
co5rqVlXK/3W3Q86Duw8hrgX+Qd5PsD9qKScJhDdaUCMhSmOmJ+P0kwORP/OKlCQ
LcD/DoBI9E5nwqgsYI7ZMHbqVlcKpEskui00zCRLN6EwQTaM1KrGPshRg8BseDE7
pB4RgmcfMiggV0k1bb797FB0K947qsCl97ytQyEZKAORPmO3a3o8s5K9HCn9AQVs
bEAuSwGVWIItASovCtEat2aQVOBcKFj5f66SJU9N9uVsdQmcug453lOBpdG1U2p4
-----END RSA PRIVATE KEY-----
';

    protected static $publicKey = '
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAx590gReUdr72YYb03uem
+aGCpO5TWFZ190SIZ5wP99xNv1mHrwvQHXyKqsyyzdBKTepKqT8MB32TSsYGX00E
S0t0iYS6U1ocXLeWVhpk9dkf5foMQlb4DfET76Kog2Xrlldh8c7g0+D5xn4zVVqw
ZjDu2teURrboVEByz+M8Ztez5BNB/R7PGWhdl+QTI18RovuC7YtlEl4gKDmBliAr
WqphyBrDRrYf1EnM/Xbq4zdC6P+D70FW5pvrpc8WkkoJmn1/zhlqz0YPGKawt1/+
r1Zx+vZO5QZhkc4Y166d9UHPNFGBeWegnWwFV8eLmVr0iK3TVCriqB+8C1pQed4v
vwIDAQAB
-----END PUBLIC KEY-----
';

    protected static $providerClass;

    protected static $keyLoaderClass;

    /**
     * Tests to create a signed JWT Token.
     */
    public function testCreate()
    {
        $keyLoaderMock = $this->getKeyLoaderMock();
        $keyLoaderMock
            ->expects($this->once())
            ->method('loadKey')
            ->with('private')
            ->willReturn(static::$privateKey);
        $keyLoaderMock
            ->expects($this->once())
            ->method('getPassphrase')
            ->willReturn('foobar');

        $payload     = ['username' => 'chalasr'];
        $jwsProvider = new static::$providerClass($keyLoaderMock, 'openssl', 'RS384', 3600, 0);

        $this->assertInstanceOf(CreatedJWS::class, $created = $jwsProvider->create($payload));

        return $created->getToken();
    }

    /**
     * Tests to verify the signature of a valid given JWT Token.
     *
     * @depends testCreate
     */
    public function testLoad($jwt)
    {
        $keyLoaderMock = $this->getKeyLoaderMock();
        $keyLoaderMock
            ->expects($this->once())
            ->method('loadKey')
            ->with('public')
            ->willReturn(static::$publicKey);

        $jwsProvider = new static::$providerClass($keyLoaderMock, 'openssl', 'RS384', 3600, 0);
        $loadedJWS   = $jwsProvider->load($jwt);
        $this->assertInstanceOf(LoadedJWS::class, $loadedJWS);

        $payload = $loadedJWS->getPayload();
        $this->assertTrue(isset($payload['exp']));
        $this->assertTrue(isset($payload['iat']));
        $this->assertTrue(isset($payload['username']));
    }

    public function testAllowEmptyTtl()
    {
        $keyLoader = $this->getKeyLoaderMock();
        $keyLoader
            ->expects($this->at(0))
            ->method('loadKey')
            ->with('private')
            ->willReturn(static::$privateKey);
        $keyLoader
            ->expects($this->at(1))
            ->method('getPassphrase')
            ->willReturn('foobar');

        $keyLoader
            ->expects($this->at(2))
            ->method('loadKey')
            ->with('public')
            ->willReturn(static::$publicKey);

        $provider = new static::$providerClass($keyLoader, 'openssl', 'RS256', null, 0);
        $jws      = $provider->create(['username' => 'chalasr']);

        $this->assertInstanceOf(CreatedJWS::class, $jws);
        $this->assertTrue($jws->isSigned());

        $jws = $provider->load($jws->getToken());

        $this->assertInstanceOf(LoadedJWS::class, $jws);
        $this->assertFalse($jws->isInvalid());
        $this->assertFalse($jws->isExpired());
        $this->assertTrue($jws->isVerified());
        $this->assertArrayNotHasKey('exp', $jws->getPayload());
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage The algorithm "wrongAlgorithm" is not supported
     */
    public function testInvalidsignatureAlgorithm()
    {
        new static::$providerClass($this->getKeyLoaderMock(), 'openssl', 'wrongAlgorithm', 3600, 0);
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage The TTL should be a numeric value
     */
    public function testInvalidTtl()
    {
        new static::$providerClass($this->getKeyLoaderMock(), 'openssl', 'wrongAlgorithm', 'invalid_ttl', 0);
    }

    private function getKeyLoaderMock()
    {
        return $this
            ->getMockBuilder(static::$keyLoaderClass)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
