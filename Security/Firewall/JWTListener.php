<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Firewall;

use Lexik\Bundle\JWTAuthenticationBundle\Extractor\AuthorizationHeaderExtractor;
use Lexik\Bundle\JWTAuthenticationBundle\Extractor\QueryParameterExtractor;
use Lexik\Bundle\JWTAuthenticationBundle\Extractor\RequestTokenExtractorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
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
    protected $securityContext;

    /**
     * @var AuthenticationManagerInterface
     */
    protected $authenticationManager;

    /**
     * @var array
     */
    protected $extractors;

    /**
     * @param SecurityContextInterface       $securityContext
     * @param AuthenticationManagerInterface $authenticationManager
     */
    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager)
    {
        $this->securityContext       = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->extractors            = array();
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
            $this->securityContext->setToken($authToken);

            return;

        } catch (AuthenticationException $failed) {

            $response = new Response();
            $response->setStatusCode(401);
            $event->setResponse($response);

        }
    }

    /**
     * @param RequestTokenExtractorInterface $extractor
     */
    public function addRequestTokenExtractor(RequestTokenExtractorInterface $extractor)
    {
        $this->extractors[] = $extractor;
    }

    /**
     * @param Request $request
     *
     * @return boolean|string
     */
    protected function getRequestToken(Request $request)
    {
        /** @var RequestTokenExtractorInterface $extractor */
        foreach ($this->extractors as $extractor) {
            if (($token = $extractor->getRequestToken($request))) {
                return $token;
            }
        }

        return false;
    }
}
