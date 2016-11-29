<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\Token\GuardTokenInterface;

/**
 * JWTUserToken.
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class JWTUserToken extends AbstractToken implements GuardTokenInterface
{
    /**
     * @var string
     */
    protected $rawToken;

    /**
     * @var string
     */
    protected $providerKey;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $roles = [], UserInterface $user = null, $rawToken = null, $providerKey = null)
    {
        parent::__construct($roles);

        if ($user) {
            $this->setUser($user);
        }

        $this->setRawToken($rawToken);
        $this->setAuthenticated(true);

        $this->providerKey = $providerKey;
    }

    /**
     * @param string $rawToken
     */
    public function setRawToken($rawToken)
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
     * Returns the provider key.
     *
     * @return string The provider key
     */
    public function getProviderKey()
    {
        return $this->providerKey;
    }
}
