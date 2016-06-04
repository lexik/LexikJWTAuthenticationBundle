<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * JWTUserToken.
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class JWTUserToken extends AbstractToken
{
    /**
     * @var string
     */
    protected $rawToken;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $roles = [])
    {
        parent::__construct($roles);

        $this->setAuthenticated(count($roles) > 0);
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
}
