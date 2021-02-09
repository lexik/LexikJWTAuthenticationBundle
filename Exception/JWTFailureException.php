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
     * @var string
     */
    private $reason;

    /**
     * @var array|null
     */
    private $payload;

    /**
     * @param string          $reason
     * @param string          $message
     * @param \Exception|null $previous
     */
    public function __construct($reason, $message, \Exception $previous = null, array $payload = null)
    {
        $this->reason = $reason;
        $this->payload = $payload;

        parent::__construct($message, 0, $previous);
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @return array|null
     */
    public function getPayload()
    {
        return $this->payload;
    }
}
