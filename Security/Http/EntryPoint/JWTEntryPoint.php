<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Http\EntryPoint;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $statusCode = 401;

        $data = [
            'code'    => $statusCode,
            'message' => 'Invalid credentials',
        ];

        $response = new JsonResponse($data, $statusCode);
        $response->headers->set('WWW-Authenticate', 'Bearer');

        return $response;
    }
}
