<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader;

/**
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
interface KeyDumperInterface
{
    /**
     * Dumps a key to be shared between parties.
     *
     * @return resource|string
     */
    public function dumpKey();
}
