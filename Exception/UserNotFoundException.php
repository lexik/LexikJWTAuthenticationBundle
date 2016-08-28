<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * User not found during authentication.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class UserNotFoundException extends AuthenticationException
{
    /**
     * @var string
     */
    private $userIdentityField;

    /**
     * @var string
     */
    private $identity;

    /**
     * @param string $userIdentityField
     * @param string $identity
     */
    public function __construct($userIdentityField, $identity)
    {
        $this->userIdentityField = $userIdentityField;
        $this->identity          = $identity;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return sprintf('Unable to load an user with property "%s" = "%s". If the user identity has changed, you must renew the token. Otherwise, verify that the "lexik_jwt_authentication.user_identity_field" config option is correctly set.', $this->userIdentityField, $this->identity);
    }
}
