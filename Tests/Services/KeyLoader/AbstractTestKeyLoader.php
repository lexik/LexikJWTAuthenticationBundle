<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Services\KeyLoader;

use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\KeyLoaderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Tests\ForwardCompatTestCaseTrait;
use PHPUnit\Framework\TestCase;

/**
 * Base class for KeyLoader classes tests.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
abstract class AbstractTestKeyLoader extends TestCase
{
    use ForwardCompatTestCaseTrait;

    /** @var KeyLoaderInterface */
    protected $keyLoader;

    /**
     * {@inheritdoc}
     */
    public function doSetUp()
    {
        $this->removeKeysIfExist();
    }

    public function testLoadKeyFromWrongType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The key type must be "public" or "private", "wrongType" given.');

        $this->keyLoader->loadKey('wrongType');
    }

    /**
     * {@inheritdoc}
     */
    public function doTearDown()
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
