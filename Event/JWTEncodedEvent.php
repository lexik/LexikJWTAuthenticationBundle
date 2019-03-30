<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

/**
 * JWTEncodedEvent.
 */
class JWTEncodedEvent extends Event
{
    /**
     * @var string
     */
    private $jwtString;

    /**
     * @param string $jwtString
     */
    public function __construct($jwtString)
    {
        $this->jwtString = $jwtString;
    }

    /**
     * @return string
     */
    public function getJWTString()
    {
        return $this->jwtString;
    }
}
