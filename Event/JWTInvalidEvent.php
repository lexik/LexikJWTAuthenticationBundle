<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

/**
 * JWTInvalidEvent.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class JWTInvalidEvent extends AuthenticationFailureEvent implements JWTFailureEventInterface
{
}
