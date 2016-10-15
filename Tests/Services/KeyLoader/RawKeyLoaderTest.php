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
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->keyLoader = new RawKeyLoader('private.pem', 'public.pem', 'foobar');

        parent::setup();
    }
}
