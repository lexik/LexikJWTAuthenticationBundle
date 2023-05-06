<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class JWTEncodedEvent extends Event
{
    private string $jwtString;

    public function __construct(string $jwtString)
    {
        $this->jwtString = $jwtString;
    }

    public function getJWTString(): string
    {
        return $this->jwtString;
    }
}
