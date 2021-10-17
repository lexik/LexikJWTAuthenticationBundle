<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\Token\GuardTokenInterface;

if (interface_exists(GuardTokenInterface::class)) {
    /**
     * Compatibility layer ensuring the guard token interface is applied when available.
     *
     * @internal
     */
    abstract class JWTCompatUserToken extends AbstractToken implements GuardTokenInterface {}
} else {
    /**
     * @internal
     */
    abstract class JWTCompatUserToken extends AbstractToken {}
}

/**
 * JWTUserToken.
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class JWTUserToken extends JWTCompatUserToken
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
    public function __construct(array $roles = [], UserInterface $user = null, $rawToken = null, $firewallName = null)
    {
        parent::__construct($roles);

        if ($user) {
            $this->setUser($user);
        }

        $this->setRawToken($rawToken);

        if (method_exists($this, 'setAuthenticated')) {
            $this->setAuthenticated(true);
        }

        $this->providerKey = $firewallName;
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
     * @deprecated since 2.10, use getFirewallName() instead
     */
    public function getProviderKey()
    {
        @trigger_error(sprintf('The "%s" method is deprecated since version 2.10 and will be removed in 3.0. Use "%s::getFirewallName()" instead.', __METHOD__, self::class), E_USER_DEPRECATED);

        return $this->getFirewallName();
    }

    /**
     * @return string
     */
    public function getFirewallName()
    {
        return $this->providerKey;
    }
}
