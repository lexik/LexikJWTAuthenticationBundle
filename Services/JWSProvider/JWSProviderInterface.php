<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider;

use Lexik\Bundle\JWTAuthenticationBundle\Signature\CreatedJWS;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\LoadedJWS;

/**
 * Interface for classes that are able to create and load JSON web signatures (JWS).
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
interface JWSProviderInterface
{
    /**
     * Creates a new JWS signature from a given payload.
     *
     * @return CreatedJWS
     */
    public function create(array $payload, array $header = []);

    /**
     * Loads an existing JWS signature from a given JWT token.
     *
     * @param string $token
     *
     * @return LoadedJWS
     */
    public function load($token);
}
