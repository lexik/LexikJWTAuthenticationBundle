<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Provider;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManagerInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * JWTProvider
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class JWTProvider implements AuthenticationProviderInterface
{
    /**
     * @var UserProviderInterface
     */
    protected $userProvider;

    /**
     * @var JWTManagerInterface
     */
    protected $jwtManager;

    /**
     * @var string
     */
    protected $userIdentityField = 'username';

    /**
     * @param UserProviderInterface $userProvider
     * @param JWTManagerInterface   $jwtManager
     */
    public function __construct(UserProviderInterface $userProvider, JWTManagerInterface $jwtManager)
    {
        $this->userProvider = $userProvider;
        $this->jwtManager   = $jwtManager;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        if (!($payload = $this->jwtManager->decode($token))) {
            throw new AuthenticationException('Invalid JWT Token');
        }

        $user = $this->getUserFromPayload($payload);

        $authToken = new JWTUserToken($user->getRoles());
        $authToken->setUser($user);

        return $authToken;
    }

    /**
     * Load user from payload, using username by default.
     * Override this to load by another property.
     *
     * @param array $payload
     *
     * @return \Symfony\Component\Security\Core\User\UserInterface
     */
    protected function getUserFromPayload(array $payload)
    {
        if (!isset($payload[$this->userIdentityField])) {
            throw new AuthenticationException('Invalid JWT Token');
        }

        return $this->userProvider->loadUserByUsername($payload[$this->userIdentityField]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof JWTUserToken;
    }
}
