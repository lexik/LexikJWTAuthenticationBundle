<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Http\EntryPoint;

use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * JWTEntryPoint starts throw a 401 when not authenticated.
 *
 * @author Jérémie Augustin <jeremie.augustin@pixel-cookers.com>
 *
 * @deprecated since 2.0, will be removed in 3.0. Use
 *             {@link JWTAuthenticator} instead
 */
class JWTEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct()
    {
        @trigger_error(sprintf('The "%s" class is deprecated since version 2.0 and will be removed in 3.0. Use "%s" instead.', __CLASS__, JWTAuthenticator::class), E_USER_DEPRECATED);
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $response = new JWTAuthenticationFailureResponse();

        return $response;
    }
}
