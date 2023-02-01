<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Services\KeyLoader;

use InvalidArgumentException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\KeyLoaderInterface;
use PHPUnit\Framework\TestCase;

/**
 * Base class for KeyLoader classes tests.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
abstract class AbstractTestKeyLoader extends TestCase
{
    protected ?KeyLoaderInterface $keyLoader = null;

    protected function setUp(): void
    {
        $this->removeKeysIfExist();

        parent::setUp();
    }

    public function testLoadKeyFromWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The key type must be "public" or "private", "wrongType" given.');

        $this->keyLoader->loadKey('wrongType');
    }

    protected function tearDown(): void
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
        $publicKey = 'public.pem';

        if (file_exists($publicKey)) {
            unlink($publicKey);
        }

        if (file_exists($privateKey)) {
            unlink($privateKey);
        }
    }
}
