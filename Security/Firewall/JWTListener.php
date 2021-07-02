<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Firewall;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * JWTListener.
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 * @author Robin Chalas  <robin.chalas@gmail.com>
 *
 * @deprecated since 2.0, will be removed in 3.0. See
 *             {@link JWTAuthenticator} instead
 */
class JWTListener
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var AuthenticationManagerInterface
     */
    protected $authenticationManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $tokenExtractors;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        array $config = []
    ) {
        @trigger_error(sprintf('The "%s" class is deprecated since version 2.0 and will be removed in 3.0. See "%s" instead.', __CLASS__, JWTAuthenticator::class), E_USER_DEPRECATED);

        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->config = array_merge(['throw_exceptions' => false], $config);
        $this->tokenExtractors = [];
    }

    public function __invoke(RequestEvent $event)
    {
        $requestToken = $this->getRequestToken($event->getRequest());

        if (null === $requestToken) {
            $jwtNotFoundEvent = new JWTNotFoundEvent();
            $this->dispatcher->dispatch($jwtNotFoundEvent, Events::JWT_NOT_FOUND);

            if ($response = $jwtNotFoundEvent->getResponse()) {
                $event->setResponse($response);
            }

            return;
        }

        try {
            $token = new JWTUserToken();
            $token->setRawToken($requestToken);

            $authToken = $this->authenticationManager->authenticate($token);
            $this->tokenStorage->setToken($authToken);

            return;
        } catch (AuthenticationException $failed) {
            if ($this->config['throw_exceptions']) {
                throw $failed;
            }

            $response = new JWTAuthenticationFailureResponse($failed->getMessage());

            $jwtInvalidEvent = new JWTInvalidEvent($failed, $response);
            $this->dispatcher->dispatch($jwtInvalidEvent, Events::JWT_INVALID);

            $event->setResponse($jwtInvalidEvent->getResponse());
        }
    }

    public function addTokenExtractor(TokenExtractorInterface $extractor)
    {
        $this->tokenExtractors[] = $extractor;
    }

    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return string
     */
    protected function getRequestToken(Request $request)
    {
        /** @var TokenExtractorInterface $tokenExtractor */
        foreach ($this->tokenExtractors as $tokenExtractor) {
            if (($token = $tokenExtractor->extract($request))) {
                return $token;
            }
        }
    }
}
