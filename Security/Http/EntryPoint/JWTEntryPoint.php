<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Http\EntryPoint;

use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * JWTEntryPoint starts throw a 401 when not authenticated.
 *
 * @author Jérémie Augustin <jeremie.augustin@pixel-cookers.com>
 */
class JWTEntryPoint implements AuthenticationEntryPointInterface
{
    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $response = new JWTAuthenticationFailureResponse();

        return $response;
    }
}
