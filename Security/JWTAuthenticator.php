<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * JWTAuthenticator
 *
 * @author Dev Lexik <dev@lexik.fr>
 */
class JWTAuthenticator implements SimplePreAuthenticatorInterface
{
    /**
     * @var JWTEncoder
     */
    protected $encoder;

    /**
     * @param JWTEncoder $encoder
     */
    public function __construct(JWTEncoder $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * {@inheritdoc}
     */
    public function createToken(Request $request, $providerKey)
    {
        if (!$request->headers->has('Authorization')) {
            throw new BadCredentialsException('No authorization header found.');
        }

        return new PreAuthenticatedToken(
            'anon.',
            $request->headers->get('Authorization'),
            $providerKey
        );
    }

    /**
     * {@inheritdoc}
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if (!preg_match('/(\S*)$/', $token->getCredentials(), $matches)) {
            throw new AuthenticationException('No Token Found');
        }

        $jwt = $matches[1];

        if (!($profile = $this->encoder->decode($jwt)) || !isset($profile['username'])) {
            throw new AuthenticationException('Invalid Token');
        }

        $user = $userProvider->loadUserByUsername($profile['username']);

        return new PreAuthenticatedToken($user, $jwt, $providerKey, $user->getRoles());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }
}
