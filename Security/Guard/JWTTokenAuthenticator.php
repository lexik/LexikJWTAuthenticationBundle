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
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\PreAuthenticationJWTUserTokenInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\PayloadAwareUserProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException as SecurityUserNotFoundException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\ChainUserProvider;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AuthenticatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * JWTTokenAuthenticator (Guard implementation).
 *
 * @see http://knpuniversity.com/screencast/symfony-rest4/jwt-guard-authenticator
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class JWTTokenAuthenticator implements AuthenticatorInterface
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
     * @var TokenStorageInterface
     */
    private $preAuthenticationTokenStorage;

    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        EventDispatcherInterface $dispatcher,
        TokenExtractorInterface $tokenExtractor,
        TokenStorageInterface $preAuthenticationTokenStorage
    ) {
        $this->jwtManager = $jwtManager;
        $this->dispatcher = $dispatcher;
        $this->tokenExtractor = $tokenExtractor;
        $this->preAuthenticationTokenStorage = $preAuthenticationTokenStorage;
    }

    public function supports(Request $request)
    {
        return false !== $this->getTokenExtractor()->extract($request);
    }

    /**
     * Returns a decoded JWT token extracted from a request.
     *
     * {@inheritdoc}
     *
     * @return PreAuthenticationJWTUserTokenInterface
     *
     * @throws InvalidTokenException If an error occur while decoding the token
     * @throws ExpiredTokenException If the request token is expired
     */
    public function getCredentials(Request $request)
    {
        $tokenExtractor = $this->getTokenExtractor();

        if (!$tokenExtractor instanceof TokenExtractorInterface) {
            throw new \RuntimeException(sprintf('Method "%s::getTokenExtractor()" must return an instance of "%s".', __CLASS__, TokenExtractorInterface::class));
        }

        if (false === ($jsonWebToken = $tokenExtractor->extract($request))) {
            return;
        }

        $preAuthToken = new PreAuthenticationJWTUserToken($jsonWebToken);

        try {
            if (!$payload = $this->jwtManager->decode($preAuthToken)) {
                throw new InvalidTokenException('Invalid JWT Token');
            }

            $preAuthToken->setPayload($payload);
        } catch (JWTDecodeFailureException $e) {
            if (JWTDecodeFailureException::EXPIRED_TOKEN === $e->getReason()) {
                $expiredTokenException = new ExpiredTokenException();
                $expiredTokenException->setToken($preAuthToken);
                throw $expiredTokenException;
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
     * @param PreAuthenticationJWTUserTokenInterface $preAuthToken Implementation of the (Security) TokenInterface
     *
     * @throws \InvalidArgumentException If preAuthToken is not of the good type
     * @throws InvalidPayloadException   If the user identity field is not a key of the payload
     * @throws UserNotFoundException     If no user can be loaded from the given token
     */
    public function getUser($preAuthToken, UserProviderInterface $userProvider)
    {
        if (!$preAuthToken instanceof PreAuthenticationJWTUserTokenInterface) {
            throw new \InvalidArgumentException(sprintf('The first argument of the "%s()" method must be an instance of "%s".', __METHOD__, PreAuthenticationJWTUserTokenInterface::class));
        }

        $payload = $preAuthToken->getPayload();
        $idClaim = $this->jwtManager->getUserIdClaim();

        if (!isset($payload[$idClaim])) {
            throw new InvalidPayloadException($idClaim);
        }

        $user = $this->loadUser($userProvider, $payload, $payload[$idClaim]);

        $this->preAuthenticationTokenStorage->setToken($preAuthToken);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $authException)
    {
        $errorMessage = strtr($authException->getMessageKey(), $authException->getMessageData());
        $response = new JWTAuthenticationFailureResponse($errorMessage);

        if ($authException instanceof ExpiredTokenException) {
            $event = new JWTExpiredEvent($authException, $response);
            $eventName = Events::JWT_EXPIRED;
        } else {
            $event = new JWTInvalidEvent($authException, $response);
            $eventName = Events::JWT_INVALID;
        }

        $this->dispatcher->dispatch($event, $eventName);

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
        $event = new JWTNotFoundEvent($exception, new JWTAuthenticationFailureResponse($exception->getMessageKey()));

        $this->dispatcher->dispatch($event, Events::JWT_NOT_FOUND);

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
     *
     * @throws \RuntimeException If there is no pre-authenticated token previously stored
     */
    public function createAuthenticatedToken(UserInterface $user, $providerKey)
    {
        $preAuthToken = $this->preAuthenticationTokenStorage->getToken();

        if (null === $preAuthToken) {
            throw new \RuntimeException('Unable to return an authenticated token since there is no pre authentication token.');
        }

        $authToken = new JWTUserToken($user->getRoles(), $user, $preAuthToken->getCredentials(), $providerKey);

        $this->dispatcher->dispatch(new JWTAuthenticatedEvent($preAuthToken->getPayload(), $authToken), Events::JWT_AUTHENTICATED);

        $this->preAuthenticationTokenStorage->setToken(null);

        return $authToken;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return false;
    }

    /**
     * Gets the token extractor to be used for retrieving a JWT token in the
     * current request.
     *
     * Override this method for adding/removing extractors to the chain one or
     * returning a different {@link TokenExtractorInterface} implementation.
     *
     * @return TokenExtractorInterface
     */
    protected function getTokenExtractor()
    {
        return $this->tokenExtractor;
    }

    /**
     * Loads the user to authenticate.
     *
     * @param UserProviderInterface $userProvider An user provider
     * @param array                 $payload      The token payload
     * @param string                $identity     The key from which to retrieve the user "username"
     *
     * @return UserInterface
     */
    protected function loadUser(UserProviderInterface $userProvider, array $payload, $identity)
    {
        if ($userProvider instanceof PayloadAwareUserProviderInterface) {
            return $userProvider->loadUserByUsernameAndPayload($identity, $payload);
        }

        if ($userProvider instanceof ChainUserProvider) {
            foreach ($userProvider->getProviders() as $provider) {
                try {
                    if ($provider instanceof PayloadAwareUserProviderInterface) {
                        return $provider->loadUserByUsernameAndPayload($identity, $payload);
                    }

                    if (method_exists($provider, 'loadUserByIdentifier')) {
                        return $provider->loadUserByIdentifier($identity);
                    }

                    return $provider->loadUserByUsername($identity);
                } catch (SecurityUserNotFoundException | UsernameNotFoundException $e) {
                    // try next one
                }
            }

            if (class_exists(SecurityUserNotFoundException::class)) {
                $ex = new SecurityUserNotFoundException(sprintf('There is no user with name "%s".', $identity));
                $ex->setUserIdentifier($identity);
            } else {
                $ex = new UsernameNotFoundException(sprintf('There is no user with name "%s".', $identity));
                $ex->setUsername($identity);
            }

            throw $ex;
        }

        if (method_exists($userProvider, 'loadUserByIdentifier')) {
            return $userProvider->loadUserByIdentifier($identity);
        }

        return $userProvider->loadUserByUsername($identity);
    }
}
