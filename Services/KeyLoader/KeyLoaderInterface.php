<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader;

/**
 * Interface for classes that are able to load crypto keys.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 *
 * @method string|null getPublicKey()
 * @method string|null getSigningKey()
 */
interface KeyLoaderInterface
{
    const TYPE_PUBLIC = 'public';

    const TYPE_PRIVATE = 'private';

    /**
     * Loads a key from a given type (public or private).
     *
     * @param resource|string|null $type
     *
     * @return resource|string|null
     */
    public function loadKey($type);

    /**
     * @return string|null
     */
    public function getPassphrase();
}
