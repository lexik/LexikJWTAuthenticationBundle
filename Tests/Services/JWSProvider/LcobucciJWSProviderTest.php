<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Services\JWSProvider;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\LcobucciJWSProvider;
use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\RawKeyLoader;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\CreatedJWS;

/**
 * Tests the LcobucciJWSProvider.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
final class LcobucciJWSProviderTest extends AbstractJWSProviderTest
{
    protected static $providerClass = LcobucciJWSProvider::class;
    protected static $keyLoaderClass = RawKeyLoader::class;

    public function testCreateWithEcdsa()
    {
        $keyLoaderMock = $this->getKeyLoaderMock();
        $keyLoaderMock
            ->expects($this->once())
            ->method('loadKey')
            ->with('private')
            ->willReturn(self::$privateKey);
        $keyLoaderMock
            ->expects($this->once())
            ->method('getPassphrase')
            ->willReturn('foobar');

        $payload     = ['username' => 'chalasr'];
        $jwsProvider = new LcobucciJWSProvider($keyLoaderMock, 'openssl', 'EC512', 3600, 0);

        $this->assertInstanceOf(CreatedJWS::class, $created = $jwsProvider->create($payload));

        return $created->getToken();
    }
}
