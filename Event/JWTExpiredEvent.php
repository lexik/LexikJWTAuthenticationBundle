<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

/**
 * JWTExpiredEvent.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class JWTExpiredEvent extends AuthenticationFailureEvent implements JWTFailureEventInterface
{
}
