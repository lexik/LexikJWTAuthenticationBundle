<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Stubs;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;

class Autowired
{
    private $jwtManager;
    private $jwtEncoder;
    private $tokenExtractor;
    private $jwsProvider;

    public function __construct(JWTTokenManagerInterface $jwtManager, JWTEncoderInterface $jwtEncoder, TokenExtractorInterface $tokenExtractor, JWSProviderInterface $jwsProvider)
    {
        $this->jwtManager = $jwtManager;
        $this->jwtEncoder = $jwtEncoder;
        $this->tokenExtractor = $tokenExtractor;
        $this->jwsProvider = $jwsProvider;
    }

    public function getJWTManager()
    {
        return $this->jwtManager;
    }

    public function getJWTEncoder()
    {
        return $this->jwtEncoder;
    }

    public function getTokenExtractor()
    {
        return $this->tokenExtractor;
    }

    public function getJWSProvider()
    {
        return $this->jwsProvider;
    }
}
