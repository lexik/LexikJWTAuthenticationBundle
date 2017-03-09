<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader;

/**
 * Reads crypto keys, mainly useful for using the phpseclib crypto engine.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class RawKeyLoader extends AbstractKeyLoader implements KeyDumperInterface
{
    /**
     * @param string $type
     *
     * @return string
     *
     * @throws \RuntimeException If the key cannot be read
     */
    public function loadKey($type)
    {
        return file_get_contents($this->getKeyPath($type));
    }

    /**
     * {@inheritdoc}
     */
    public function dumpKey()
    {
        return $this->loadKey('public');
    }
}
