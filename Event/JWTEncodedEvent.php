<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class JWTEncodedEvent extends Event
{
    private $jwtString;

    public function __construct(string $jwtString)
    {
        $this->jwtString = $jwtString;
    }

    public function getJWTString()
    {
        return $this->jwtString;
    }
}
