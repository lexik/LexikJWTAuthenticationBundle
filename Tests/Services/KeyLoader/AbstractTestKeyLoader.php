<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Services\KeyLoader;

use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\AbstractKeyLoader;
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

    public function testLoadingNullAdditionalPublicKey()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Additional public key is not set correctly. Check the "lexik_jwt_authentication.additional_public_keys" configuration key');

        $className = $this->getClassName();
        /** @var AbstractKeyLoader $loader */
        $loader = new $className('private.pem', 'public.pem', 'foobar', [null]);
        $loader->getAdditionalPublicKeys();
    }

    public function testLoadingAdditionalPublicKeysAsStrings()
    {
        $additionalPublicKeys = ['myKeyText1', 'myKeyText2'];

        $className = $this->getClassName();
        /** @var AbstractKeyLoader $loader */
        $loader = new $className('private.pem', 'public.pem', 'foobar', $additionalPublicKeys);

        $this->assertSame($additionalPublicKeys, $loader->getAdditionalPublicKeys());
    }

    public function testLoadingAdditionalPublicKeysFromFiles()
    {
        file_put_contents('additional-public-1.pem', 'myKeyTextFromFile1');
        file_put_contents('additional-public-2.pem', 'myKeyTextFromFile2');

        $className = $this->getClassName();
        /** @var AbstractKeyLoader $loader */
        $loader = new $className('private.pem', 'public.pem', 'foobar', ['additional-public-1.pem', 'additional-public-2.pem']);

        $this->assertSame(['myKeyTextFromFile1', 'myKeyTextFromFile2'], $loader->getAdditionalPublicKeys());
    }

    public function testLoadingAdditionalPublicKeysFromFilesAndAsStrings()
    {
        file_put_contents('additional-public-1.pem', 'myKeyTextFromFile1');

        $className = $this->getClassName();
        /** @var AbstractKeyLoader $loader */
        $loader = new $className('private.pem', 'public.pem', 'foobar', ['additional-public-1.pem', 'myKeyText2']);

        $this->assertSame(['myKeyTextFromFile1', 'myKeyText2'], $loader->getAdditionalPublicKeys());
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
        $keys = ['private.pem', 'public.pem', 'additional-public-1.pem', 'additional-public-2.pem'];
        foreach ($keys as $key) {
            if (file_exists($key)) {
                unlink($key);
            }
        }
    }

    abstract protected function getClassName(): string;
}
