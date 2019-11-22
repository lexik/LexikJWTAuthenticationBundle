<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Firewall;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractsEventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

/**
 * JWTListener.
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 * @author Robin Chalas  <robin.chalas@gmail.com>
 *
 * @deprecated since 2.0, will be removed in 3.0. See
 *             {@link JWTTokenAuthenticator} instead
 */
class JWTListener extends AbstractListener
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

    /**
     * @param TokenStorageInterface          $tokenStorage
     * @param AuthenticationManagerInterface $authenticationManager
     * @param array                          $config
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        array $config = []
    ) {
        @trigger_error(sprintf('The "%s" class is deprecated since version 2.0 and will be removed in 3.0. See "%s" instead.', __CLASS__, JWTTokenAuthenticator::class), E_USER_DEPRECATED);

        $this->tokenStorage          = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->config                = array_merge(['throw_exceptions' => false], $config);
        $this->tokenExtractors       = [];
    }

    /**
     * @param GetResponseEvent|RequestEvent $event
     */
    public function __invoke($event)
    {
        $requestToken = $this->getRequestToken($event->getRequest());

        if (null === $requestToken) {
            $jwtNotFoundEvent = new JWTNotFoundEvent();
            if ($this->dispatcher instanceof ContractsEventDispatcherInterface) {
                $this->dispatcher->dispatch($jwtNotFoundEvent, Events::JWT_NOT_FOUND);
            } else {
                $this->dispatcher->dispatch(Events::JWT_NOT_FOUND, $jwtNotFoundEvent);
            }


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
            if ($this->dispatcher instanceof ContractsEventDispatcherInterface) {
                $this->dispatcher->dispatch($jwtInvalidEvent, Events::JWT_INVALID);
            } else {
                $this->dispatcher->dispatch(Events::JWT_INVALID, $jwtInvalidEvent);
            }


            $event->setResponse($jwtInvalidEvent->getResponse());
        }
    }

    /**
     * @param TokenExtractorInterface $extractor
     */
    public function addTokenExtractor(TokenExtractorInterface $extractor)
    {
        $this->tokenExtractors[] = $extractor;
    }

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param Request $request
     *
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
