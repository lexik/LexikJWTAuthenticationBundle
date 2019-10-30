<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Services\JWSProvider;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\DefaultJWSProvider;
use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\KeyLoaderInterface;

/**
 * Tests the DefaultJWSProvider.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 *
 * @group legacy
 */
final class DefaultJWSProviderTest extends AbstractJWSProviderTest
{
    public static function setUpBeforeClass()
    {
        self::$providerClass  = DefaultJWSProvider::class;
        self::$keyLoaderClass = KeyLoaderInterface::class;
    }
}
