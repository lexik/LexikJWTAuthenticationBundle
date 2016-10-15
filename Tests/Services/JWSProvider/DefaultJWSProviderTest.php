<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Services\JWSProvider;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\DefaultJWSProvider;

/**
 * Tests the DefaultJWSProvider.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
final class DefaultJWSProviderTest extends AbstractJWSProviderTest
{
    public function __construct()
    {
        self::$providerClass = DefaultJWSProvider::class;
    }
}
