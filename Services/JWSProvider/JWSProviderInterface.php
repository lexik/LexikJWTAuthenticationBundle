<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider;

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
     * @param array $payload
     *
     * @return \Lexik\Bundle\JWTAuthenticationBundle\Signature\CreatedJWS
     */
    public function create(array $payload);

    /**
     * Loads an existing JWS signature from a given JWT token.
     *
     * @param string $token
     *
     * @return \Lexik\Bundle\JWTAuthenticationBundle\Signature\LoadedJWS
     */
    public function load($token);
}
