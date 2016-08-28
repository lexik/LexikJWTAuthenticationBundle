<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Guard;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\ExpiredTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidPayloadException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\MissingTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\UserNotFoundException;
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
     * @throws InvalidTokenException If the request token cannot be decoded
     * @throws ExpiredTokenException If the request token is expired
     */
    public function getCredentials(Request $request)
    {
        if (false === ($jsonWebToken = $this->tokenExtractor->extract($request))) {
            return;
        }

        $preAuthToken = new PreAuthenticationJWTUserToken($jsonWebToken);

        try {
            if (!$payload = $this->jwtManager->decode($preAuthToken)) {
                throw new InvalidTokenException();
            }

            $preAuthToken->setPayload($payload);
        } catch (JWTDecodeFailureException $e) {
            if (JWTDecodeFailureException::EXPIRED_TOKEN === $e->getReason()) {
                throw new ExpiredTokenException();
            }

            throw new InvalidTokenException('Invalid JWT Token', 0, $e);
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
            throw new InvalidPayloadException($identityField);
        }

        $identity = $payload[$identityField];

        try {
            $user = $userProvider->loadUserByUsername($identity);
        } catch (UsernameNotFoundException $e) {
            throw new UserNotFoundException($identityField, $identity);
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
        $response = new JWTAuthenticationFailureResponse($authException->getMessageKey());

        if ($authException instanceof ExpiredTokenException) {
            $event = new JWTExpiredEvent($authException, $response);
            $this->dispatcher->dispatch(Events::JWT_EXPIRED, $event);
        } else {
            $event = new JWTInvalidEvent($authException, $response);
            $this->dispatcher->dispatch(Events::JWT_INVALID, $event);
        }

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
        $exception = new MissingTokenException('JWT Token not found', 0, $authException);
        $event     = new JWTNotFoundEvent($exception, new JWTAuthenticationFailureResponse($exception->getMessageKey()));

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
