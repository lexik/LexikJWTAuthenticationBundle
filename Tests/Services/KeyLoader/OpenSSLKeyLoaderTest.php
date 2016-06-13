<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Services\KeyLoader;

use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\OpenSSLKeyLoader;

/**
 * OpenSSLKeyLoaderTest.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
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

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Failed to load public key "public.pem".
     */
    public function testLoadInvalidPublicKey()
    {
        touch('public.pem');

        $this->keyLoader->loadKey('public');
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Failed to load private key "private.pem".
     */
    public function testLoadInvalidPrivateKey()
    {
        touch('private.pem');

        $this->keyLoader->loadKey('private');
    }
}
