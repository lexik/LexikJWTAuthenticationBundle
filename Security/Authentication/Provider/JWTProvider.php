<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Provider;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * JWTProvider.
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 *
 * @deprecated since 2.0, will be removed in 3.0. See
 *             {@link JWTTokenAuthenticator} instead
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
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var string
     */
    protected $userIdentityField;

    /**
     * @param UserProviderInterface    $userProvider
     * @param JWTManagerInterface      $jwtManager
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        UserProviderInterface $userProvider,
        JWTManagerInterface $jwtManager,
        EventDispatcherInterface $dispatcher
    ) {
        @trigger_error(sprintf('The "%s" class is deprecated since version 2.0 and will be removed in 3.0. See "%s" instead.', __CLASS__, JWTTokenAuthenticator::class), E_USER_DEPRECATED);

        $this->userProvider      = $userProvider;
        $this->jwtManager        = $jwtManager;
        $this->dispatcher        = $dispatcher;
        $this->userIdentityField = 'username';
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        try {
            if (!$payload = $this->jwtManager->decode($token)) {
                throw $this->createAuthenticationException();
            }
        } catch (JWTDecodeFailureException $e) {
            throw $this->createAuthenticationException($e);
        }

        $user = $this->getUserFromPayload($payload);

        $authToken = new JWTUserToken($user->getRoles());
        $authToken->setUser($user);
        $authToken->setRawToken($token->getCredentials());

        $event = new JWTAuthenticatedEvent($payload, $authToken);
        $this->dispatcher->dispatch(Events::JWT_AUTHENTICATED, $event);

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
            throw $this->createAuthenticationException();
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

    /**
     * @return string
     */
    public function getUserIdentityField()
    {
        return $this->userIdentityField;
    }

    /**
     * @param string $userIdentityField
     */
    public function setUserIdentityField($userIdentityField)
    {
        $this->userIdentityField = $userIdentityField;
    }

    /**
     * @param JWTDecodeFailureException $previous
     *
     * @return AuthenticationException
     */
    private function createAuthenticationException(JWTDecodeFailureException $previous = null)
    {
        $message = (null === $previous) ? 'Invalid JWT Token' : $previous->getMessage();

        return new AuthenticationException($message, 401, $previous);
    }
}
