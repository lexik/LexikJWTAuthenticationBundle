<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Services\KeyLoader;

use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\SecLibKeyLoader;

/**
 * SecLibKeyLoaderTest.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class SecLibKeyLoaderTest extends AbstractTestKeyLoader
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->keyLoader = new SecLibKeyLoader('private.pem', 'public.pem', 'foobar');

        parent::setup();
    }
}
