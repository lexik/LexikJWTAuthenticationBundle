<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class JWTEncodedEvent
 * @package Lexik\Bundle\JWTAuthenticationBundle\Event
 */
class JWTEncodedEvent extends Event
{

    /**
     * @var $jwtString
     */
    private $jwtString;

    /**
     * @param $jwtString
     */
    public function __construct($jwtString)
    {
        $this->jwtString = $jwtString;
    }

    /**
     * @return $jwtString
     */
    public function getJWTString()
    {
        return $this->jwtString;
    }
}