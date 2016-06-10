<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Utils;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

final class ExpAwareJWTEncoderWrapper
{
    private $wrappedEncoder;

    public function __construct(JWTEncoderInterface $encoder)
    {
        $this->wrappedEncoder = $encoder;
    }

    public function decreaseTokenExpirationTime($token)
    {
        $payload        = $this->wrappedEncoder->decode($token);
        $payload['exp'] = time();

        return $this->wrappedEncoder->encode($payload);
    }
}
