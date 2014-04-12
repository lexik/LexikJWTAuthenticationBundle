<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Firewall;

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
     * @var string
     */
    protected $headerPrefix;

    /**
     * @param SecurityContextInterface       $securityContext
     * @param AuthenticationManagerInterface $authenticationManager
     * @param string                         $headerPrefix
     */
    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager, $headerPrefix)
    {
        $this->securityContext       = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->headerPrefix          = $headerPrefix;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(GetResponseEvent $event)
    {
        if (!($raw = $this->getRawTokenFromRequest($event->getRequest()))) {
            return;
        }

        $token = new JWTUserToken();
        $token->setRawToken($raw);

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
     * @param Request $request
     *
     * @return boolean|string
     */
    protected function getRawTokenFromRequest(Request $request)
    {
        if (!$request->headers->has('Authorization')) {
            return false;
        }

        $headerParts = explode(' ', $request->headers->get('Authorization'));

        if (!(count($headerParts) === 2 && $headerParts[0] === $this->headerPrefix)) {
            return false;
        }

        return $headerParts[1];
    }
}
