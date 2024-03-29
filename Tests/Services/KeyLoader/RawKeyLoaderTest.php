<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Services\KeyLoader;

use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\RawKeyLoader;

/**
 * RawKeyLoaderTest.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class RawKeyLoaderTest extends AbstractTestKeyLoader
{
    protected function setUp(): void
    {
        $this->keyLoader = new RawKeyLoader('private.pem', 'public.pem', 'foobar');

        parent::setUp();
    }

    public function testLoadPublicKey()
    {
        $this->assertSame('public.pem', $this->keyLoader->loadKey('public'));
    }

    public function testLoadPrivateKey()
    {
        $this->assertSame('private.pem', $this->keyLoader->loadKey('private'));
    }
}
