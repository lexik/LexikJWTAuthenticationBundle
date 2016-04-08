<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services;

/**
 * Interface for JWS provider classes.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
interface JWSProviderInterface
{
    const SIGNED   = 'signed';
    const VERIFIED = 'verified';

    /**
     * Creates a new JWS signature.
     *
     * @param array $payload
     *
     * @return JWSProviderInterface
     */
    public function createSignedToken(array $payload);

    /**
     * Loads a JWS signature from a given JWT token.
     *
     * @param string $token
     *
     * @return JWSProviderInterface
     */
    public function loadSignature($token);

    /**
     * Provides a JWT token from a newly created signature.
     *
     * @return string
     */
    public function getToken();
}
