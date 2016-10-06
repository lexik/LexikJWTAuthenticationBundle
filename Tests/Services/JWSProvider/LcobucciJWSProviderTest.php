<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Services\JWSProvider;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\LcobucciJWSProvider;

/**
 * Tests the LcobucciJWSProvider.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
final class LcobucciJWSProviderTest extends AbstractJWSProviderTest
{
    public function __construct()
    {
        self::$providerClass = LcobucciJWSProvider::class;
    }
}
