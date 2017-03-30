<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Response sent on successful JWT authentication.
 *
 * @internal
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
final class JWTAuthenticationSuccessResponse extends JsonResponse
{
    /**
     * The Json Web Token.
     *
     * Immutable property.
     *
     * @var string
     */
    private $token;

    /**
     * @param string $token Json Web Token
     * @param array  $data  Extra data passed to the response
     */
    public function __construct($token, array $data = [])
    {
        parent::__construct(['token' => $token] + $data);
    }
}
