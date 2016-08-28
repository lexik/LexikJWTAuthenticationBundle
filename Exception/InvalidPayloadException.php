<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Missing key in the token payload during authentication.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class InvalidPayloadException extends AuthenticationException
{
    /**
     * @var string
     */
    private $invalidKey;

    /**
     * @param string $invalidKey The key that cannot be found in the payload
     */
    public function __construct($invalidKey)
    {
        $this->invalidKey = $invalidKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return sprintf('Unable to find key "%s" in the token payload.', $this->invalidKey);
    }
}
