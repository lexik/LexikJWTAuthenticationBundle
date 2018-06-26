<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Stubs;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationFailureHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;

class Autowired
{
    private $jwtManager;
    private $jwtEncoder;
    private $tokenExtractor;
    private $jwsProvider;
    private $authenticationSuccessHandler;
    private $authenticationFailureHandler;

    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        JWTEncoderInterface $jwtEncoder,
        TokenExtractorInterface $tokenExtractor,
        JWSProviderInterface $jwsProvider,
        AuthenticationSuccessHandler $authenticationSuccessHandler,
        AuthenticationFailureHandler $authenticationFailureHandler
    ) {
        $this->jwtManager = $jwtManager;
        $this->jwtEncoder = $jwtEncoder;
        $this->tokenExtractor = $tokenExtractor;
        $this->jwsProvider = $jwsProvider;
        $this->authenticationSuccessHandler = $authenticationSuccessHandler;
        $this->authenticationFailureHandler = $authenticationFailureHandler;
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

    public function getAuthenticationSuccessHandler()
    {
        return $this->authenticationSuccessHandler;
    }

    public function getAuthenticationFailureHandler()
    {
        return $this->authenticationFailureHandler;
    }
}
