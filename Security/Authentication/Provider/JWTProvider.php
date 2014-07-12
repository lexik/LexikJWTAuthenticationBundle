<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Provider;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
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
    private $userProvider;

    /**
     * @var JWTManager
     */
    private $jwtManager;

    /**
     * @param UserProviderInterface $userProvider
     * @param JWTManager            $jwtManager
     */
    public function __construct(UserProviderInterface $userProvider, JWTManager $jwtManager)
    {
        $this->userProvider = $userProvider;
        $this->jwtManager   = $jwtManager;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        $payload = $this->jwtManager->decode($token);

        if (!$payload) {
            throw new AuthenticationException('Invalid JWT Token');
        }

        if (!isset($payload['username'])) {
            throw new AuthenticationException('No username found in token.');
        }

        if (!($user = $this->userProvider->loadUserByUsername($payload['username']))) {
            throw new AuthenticationException('User "' . $payload['username'] . '" could not be found.');
        }

        $authToken = new JWTUserToken($user->getRoles());
        $authToken->setUser($user);

        return $authToken;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof JWTUserToken;
    }
}
