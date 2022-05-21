<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Exception;

/**
 * JWTDecodeFailureException is thrown if an error occurs in the token decoding process.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class JWTDecodeFailureException extends JWTFailureException
{
    public const INVALID_TOKEN = 'invalid_token';

    public const UNVERIFIED_TOKEN = 'unverified_token';

    public const EXPIRED_TOKEN = 'expired_token';
}
