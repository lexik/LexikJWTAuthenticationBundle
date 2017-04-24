<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Stubs;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class Autowired
{
    private $jwtManager;
    private $jwtEncoder;

    public function __construct(JWTTokenManagerInterface $jwtManager, JWTEncoderInterface $jwtEncoder)
    {
        $this->jwtManager = $jwtManager;
        $this->jwtEncoder = $jwtEncoder;
    }

    public function getJWTManager()
    {
        return $this->jwtManager;
    }

    public function getJWTEncoder()
    {
        return $this->jwtEncoder;
    }
}
