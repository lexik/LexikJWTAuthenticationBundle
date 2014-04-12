<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Provider;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoder;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
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
     * @var JWTEncoder
     */
    private $encoder;

    /**
     * @param UserProviderInterface $userProvider
     * @param JWTEncoder            $encoder
     */
    public function __construct(UserProviderInterface $userProvider, JWTEncoder $encoder)
    {
        $this->userProvider = $userProvider;
        $this->encoder      = $encoder;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        $jwt = $this->encoder->decode($token->getCredentials());

        if (!($jwt && isset($jwt['username']))) {
            throw new AuthenticationException('Invalid JWT Token');
        }

        if (!($user = $this->userProvider->loadUserByUsername($jwt['username']))) {
            throw new AuthenticationException('User "' . $jwt['username'] . '" could not be found.');
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
