<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Services;

use Lexik\Bundle\JWTAuthenticationBundle\Services\OpenSSLKeyLoader;

/**
 * OpenSSLKeyLoaderTest
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class OpenSSLKeyLoaderTest extends \PHPUnit_Framework_TestCase
{
    /** @var OpenSSLKeyLoader */
    protected $keyLoader;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->keyLoader = new OpenSSLKeyLoader('private.pem', 'public.pem', 'foobar');
    }

    /**
     * Test load unreadable public key.
     *
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Public key "public.pem" does not exist or is not readable.
     */
    public function testLoadUnreadablePublicKey()
    {
        $this->keyLoader->loadKey('public');
    }

    /**
     * Test load unreadable private key.
     *
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Private key "private.pem" does not exist or is not readable.
     */
    public function testLoadUnreadablePrivateKey()
    {
        $this->keyLoader->loadKey('private');
    }

    /**
     * Test load unreadable private key.
     *
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Failed to load public key "public.pem".
     */
    public function testLoadInvalidPublicKey()
    {
        touch('public.pem');

        $this->keyLoader->loadKey('public');
    }

    /**
     * Test load unreadable private key.
     *
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Failed to load private key "private.pem".
     */
    public function testLoadInvalidPrivateKey()
    {
        touch('private.pem');

        $this->keyLoader->loadKey('private');
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        $privateKey = 'private.pem';
        $publicKey  = 'public.pem';

        if (file_exists($publicKey)) {
            unlink($publicKey);
        }

        if (file_exists($privateKey)) {
            unlink($privateKey);
        }
    }
}
