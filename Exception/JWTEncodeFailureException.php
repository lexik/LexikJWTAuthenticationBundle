<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Exception;

/**
 * JWTEncodeFailureException is thrown if an error occurs in the token encoding process.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class JWTEncodeFailureException extends JWTFailureException
{
    public const INVALID_CONFIG = 'invalid_config';

    public const UNSIGNED_TOKEN = 'unsigned_token';
}
