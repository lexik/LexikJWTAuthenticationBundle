<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token;

use Symfony\Component\Security\Guard\Token\PreAuthenticationGuardToken;

/**
 * PreAuthenticationJWTUserToken.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
final class PreAuthenticationJWTUserToken extends PreAuthenticationGuardToken implements PreAuthenticationJWTUserTokenInterface
{
    /**
     * @var string
     */
    private $rawToken;

    /**
     * @var array
     */
    private $payload;

    /**
     * @param string $rawToken
     */
    public function __construct($rawToken)
    {
        $this->rawToken = $rawToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials()
    {
        return $this->rawToken;
    }

    /**
     * {@inheritdoc}
     */
    public function setPayload(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayload()
    {
        return $this->payload;
    }
}
