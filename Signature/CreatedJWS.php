<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Signature;

/**
 * Object representation of a newly created JSON Web Signature.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
final class CreatedJWS
{
    /**
     * @deprecated since v2.11
     */
    const SIGNED = 'signed';

    private $token;
    private $signed;

    public function __construct(string $token, bool $isSigned)
    {
        $this->token = $token;
        $this->signed = $isSigned;
    }

    public function isSigned(): bool
    {
        return $this->signed;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
