<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader;

/**
 * Load config keys for the phpseclib encryption engine.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class SecLibKeyLoader extends AbstractKeyLoader
{
    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException If the key cannot be read
     */
    public function loadKey($type)
    {
        return file_get_contents($this->getKeyPath($type));
    }
}
