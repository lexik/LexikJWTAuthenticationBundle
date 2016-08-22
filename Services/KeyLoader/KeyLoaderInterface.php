<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader;

/**
 * Interface for classes that are able to load crypto keys.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
interface KeyLoaderInterface
{
    /**
     * Loads a key from a given type (public or private).
     *
     * @param resource|string
     *
     * @return resource|string
     */
    public function loadKey($type);

    /**
     * @return string
     */
    public function getPassphrase();
}
