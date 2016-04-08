<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Provider;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailure\JWTDecodeFailureException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * JWTProvider.
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
                throw $this->createInvalidJWTException();
            }
        } catch (JWTDecodeFailureException $e) {
            throw $this->createInvalidJWTException($e);
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
            throw $this->createInvalidJWTException();
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
    private function createInvalidJWTException(JWTDecodeFailureException $previous = null, $message = 'Invalid JWT Token')
    {
        $message = (null === $previous) ? $message :  $previous->getMessage();

        return new AuthenticationException($message, 401, $previous);
    }
}
