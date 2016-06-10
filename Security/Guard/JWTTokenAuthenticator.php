<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Guard;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTAuthenticationException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\PreAuthenticationJWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * JWTTokenAuthenticator (Guard implementation).
 *
 * @see http://knpuniversity.com/screencast/symfony-rest4/jwt-guard-authenticator
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class JWTTokenAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var JWTTokenManagerInterface
     */
    private $jwtManager;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var TokenExtractorInterface
     */
    private $tokenExtractor;

    /**
     * @param JWTTokenManagerInterface $jwtManager
     * @param EventDispatcherInterface $dispatcher
     * @param TokenExtractorInterface  $tokenExtractor
     */
    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        EventDispatcherInterface $dispatcher,
        TokenExtractorInterface $tokenExtractor
    ) {
        $this->jwtManager     = $jwtManager;
        $this->dispatcher     = $dispatcher;
        $this->tokenExtractor = $tokenExtractor;
    }

    /**
     * Returns a decoded JWT token extracted from a request.
     *
     * {@inheritdoc}
     *
     * @return PreAuthenticationJWTUserToken
     *
     * @throws JWTAuthenticationException If the request token cannot be decoded
     */
    public function getCredentials(Request $request)
    {
        if (false === ($jsonWebToken = $this->tokenExtractor->extract($request))) {
            return;
        }

        $preAuthToken = new PreAuthenticationJWTUserToken($jsonWebToken);

        try {
            if (!$payload = $this->jwtManager->decode($preAuthToken)) {
                throw JWTAuthenticationException::invalidToken();
            }

            $preAuthToken->setPayload($payload);
        } catch (JWTDecodeFailureException $e) {
            throw JWTAuthenticationException::invalidToken($e);
        }

        return $preAuthToken;
    }

    /**
     * Returns an user object loaded from a JWT token.
     *
     * {@inheritdoc}
     *
     * @param PreAuthenticationJWTUserToken Implementation of the (Security) TokenInterface
     *
     * @throws JWTAuthenticationException If no user can be loaded from the decoded token
     */
    public function getUser($preAuthToken, UserProviderInterface $userProvider)
    {
        if (!$preAuthToken instanceof PreAuthenticationJWTUserToken) {
            throw new \InvalidArgumentException(
                sprintf('The first argument of the "%s()" method must be an instance of "%s".', __METHOD__, PreAuthenticationJWTUserToken::class)
            );
        }

        $payload       = $preAuthToken->getPayload();
        $identityField = $this->jwtManager->getUserIdentityField();

        if (!isset($payload[$identityField])) {
            throw JWTAuthenticationException::invalidPayload(
                sprintf('Unable to find a key corresponding to the configured user_identity_field ("%s") in the token payload.', $identityField)
            );
        }

        $identity = $payload[$identityField];

        try {
            $user = $userProvider->loadUserByUsername($identity);
        } catch (UsernameNotFoundException $e) {
            throw JWTAuthenticationException::invalidUser($identity, $identityField);
        }

        $authToken = new JWTUserToken($user->getRoles());
        $authToken->setUser($user);
        $authToken->setRawToken($preAuthToken->getCredentials());

        $this->dispatcher->dispatch(Events::JWT_AUTHENTICATED, new JWTAuthenticatedEvent($payload, $authToken));

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $authException)
    {
        $event = new JWTInvalidEvent($authException, new JWTAuthenticationFailureResponse($authException->getMessage()));
        $this->dispatcher->dispatch(Events::JWT_INVALID, $event);

        return $event->getResponse();
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return;
    }

    /**
     * {@inheritdoc}
     *
     * @return JWTAuthenticationFailureResponse
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $authException = JWTAuthenticationException::tokenNotFound();
        $event         = new JWTNotFoundEvent($authException, new JWTAuthenticationFailureResponse($authException->getMessage()));

        $this->dispatcher->dispatch(Events::JWT_NOT_FOUND, $event);

        return $event->getResponse();
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return false;
    }
}
