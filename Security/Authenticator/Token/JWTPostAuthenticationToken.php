<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\Token;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

class JWTPostAuthenticationToken extends PostAuthenticationToken
{
    private $token;

    public function __construct(UserInterface $user, string $firewallName, array $roles, string $token)
    {
        parent::__construct($user, $firewallName, $roles);

        $this->token = $token;
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(): string
    {
        return $this->token;
    }
}
