<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Services\KeyLoader;

use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\KeyLoaderInterface;

/**
 * Base class for KeyLoader classes tests.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
abstract class AbstractTestKeyLoader extends \PHPUnit_Framework_TestCase
{
    /** @var KeyLoaderInterface */
    protected $keyLoader;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->removeKeysIfExist();
    }

    /**
     * Test load unreadable public key.
     *
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Public key "public.pem" does not exist or is not readable.
     */
    public function testLoadUnreadablePublicKey()
    {
        $this->keyLoader->loadKey('public');
    }

    /**
     * Test load unreadable private key.
     *
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Private key "private.pem" does not exist or is not readable.
     */
    public function testLoadUnreadablePrivateKey()
    {
        $this->keyLoader->loadKey('private');
    }

    /**
     * Test load key from invalid type.
     *
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage The key type must be "public" or "private", "wrongType" given.
     */
    public function testLoadKeyFromWrongType()
    {
        $this->keyLoader->loadKey('wrongType');
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        $this->removeKeysIfExist();
    }

    /**
     * Remove any generated key.
     *
     * @internal
     */
    protected function removeKeysIfExist()
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
