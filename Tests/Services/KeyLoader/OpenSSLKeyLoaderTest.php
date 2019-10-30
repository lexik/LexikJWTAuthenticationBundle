<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Services\KeyLoader;

use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\OpenSSLKeyLoader;

/**
 * OpenSSLKeyLoaderTest.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 *
 * @group legacy
 */
class OpenSSLKeyLoaderTest extends AbstractTestKeyLoader
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->keyLoader = new OpenSSLKeyLoader('private.pem', 'public.pem', 'foobar');

        parent::setup();
    }

    public function testLoadInvalidPublicKey()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('no start line');

        touch('public.pem');

        $this->keyLoader->loadKey('public');
    }

    public function testLoadInvalidPrivateKey()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('no start line');

        touch('private.pem');

        $this->keyLoader->loadKey('private');
    }
}
