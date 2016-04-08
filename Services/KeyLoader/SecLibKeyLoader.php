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
     * @throws \RuntimeException If the key cannot be read.
     */
    public function loadKey($type)
    {
        $path = $this->getKeyPath($type);

        if (!file_exists($path) || !is_readable($path)) {
            throw $this->createUnreadableKeyException($type, $path);
        }

        return file_get_contents($path);
    }
}
