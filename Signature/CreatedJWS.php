<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Signature;

/**
 * Object representation of a newly created JSON Web Signature.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
final class CreatedJWS
{
    const SIGNED = 'signed';

    /**
     * The JSON Web Token.
     *
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $state;

    /**
     * @param string $token
     * @param bool   $isSigned
     */
    public function __construct($token, $isSigned)
    {
        $this->token = $token;

        if (true === $isSigned) {
            $this->state = self::SIGNED;
        }
    }

    /**
     * @return bool
     */
    public function isSigned()
    {
        return self::SIGNED === $this->state;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
}
