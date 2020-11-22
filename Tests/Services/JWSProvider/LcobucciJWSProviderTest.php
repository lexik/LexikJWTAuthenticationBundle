<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Services\JWSProvider;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\LcobucciJWSProvider;
use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\RawKeyLoader;

/**
 * Tests the LcobucciJWSProvider.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
final class LcobucciJWSProviderTest extends AbstractJWSProviderTest
{
    protected static $providerClass  = LcobucciJWSProvider::class;
    protected static $keyLoaderClass = RawKeyLoader::class;
}
