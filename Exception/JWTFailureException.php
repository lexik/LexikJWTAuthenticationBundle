<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Exception;

/**
 * Base class for exceptions thrown during JWT creation/loading.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class JWTFailureException extends \Exception
{
    /**
     * @param string          $message
     * @param \Exception|null $previous
     */
    public function __construct($message, \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
