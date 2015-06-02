<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Firewall;

use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

/**
 * JWTListener
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class JWTListener implements ListenerInterface
{
    /**
     * @var SecurityContextInterface
     */
    protected $tokenStorage;

    /**
     * @var AuthenticationManagerInterface
     */
    protected $authenticationManager;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $tokenExtractors;

    /**
     * @param TokenStorageIterface           $tokenStorage
     * @param AuthenticationManagerInterface $authenticationManager
     * @param array                          $config
     */
    public function __construct(TokenStorageInterface  $tokenStorage, AuthenticationManagerInterface $authenticationManager, array $config = array())
    {
        $this->tokenStorage          = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->config                = array_merge(array('throw_exceptions' => false), $config);
        $this->tokenExtractors       = array();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(GetResponseEvent $event)
    {
        if (!($requestToken = $this->getRequestToken($event->getRequest()))) {
            return;
        }

        $token = new JWTUserToken();
        $token->setRawToken($requestToken);

        try {

            $authToken = $this->authenticationManager->authenticate($token);
            $this->tokenStorage->setToken($authToken);

            return;

        } catch (AuthenticationException $failed) {
            if ($this->config['throw_exceptions']) {
                throw $failed;
            }

            $response = new Response();
            $response->setStatusCode(401);
            $event->setResponse($response);
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
     * @param Request $request
     *
     * @return boolean|string
     */
    protected function getRequestToken(Request $request)
    {
        /** @var TokenExtractorInterface $tokenExtractor */
        foreach ($this->tokenExtractors as $tokenExtractor) {
            if (($token = $tokenExtractor->extract($request))) {
                return $token;
            }
        }

        return false;
    }
}
