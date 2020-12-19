<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Exception;

/**
 * Base class for exceptions thrown during JWT creation/loading.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class JWTFailureException extends \Exception
{
    private $reason;
    private $payload;

    public function __construct(string $reason, string $message, \Throwable $previous = null, array $payload = null)
    {
        $this->reason = $reason;
        $this->payload = $payload;

        parent::__construct($message, 0, $previous);
    }

    public function getReason()
    {
        return $this->reason;
    }

    public function getPayload()
    {
        return $this->payload;
    }
}
