<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Response sent on successful JWT authentication.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
final class JWTAuthenticationSuccessResponse extends JsonResponse
{
    /**
     * @param string $token Json Web Token
     * @param array  $data  Extra data passed to the response
     */
    public function __construct(string $token, array $data = [], array $jwtCookies = [])
    {
        if (!$jwtCookies) {
            parent::__construct(['token' => $token] + $data);

            return;
        }

        parent::__construct($data);

        foreach ($jwtCookies as $cookie) {
            $this->headers->setCookie($cookie);
        }
    }
}
