<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\AccessToken;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;

/**
 * @final
 */
class JWTHandler implements AccessTokenHandlerInterface
{
    /**
     * @var JWTTokenManagerInterface
     */
    private $jwtManager;

    public function __construct(
        JWTTokenManagerInterface $jwtManager
    )
    {
        $this->jwtManager = $jwtManager;
    }

    public function getUserIdentifierFrom(string $accessToken): string
    {

        try {
            if (!$payload = $this->jwtManager->parse($accessToken)) {
                throw new AuthenticationException('Invalid JWT Token');
            }
        } catch (\Throwable $e) {
            throw new AuthenticationException('Invalid JWT Token', 0, $e);
        }

        $idClaim = $this->jwtManager->getUserIdClaim();
        if (!isset($payload[$idClaim])) {
            throw new AuthenticationException(sprintf('Unable to find key "%s" in the token payload.', $idClaim));
        }
        if (!is_string($payload[$idClaim]) || $payload[$idClaim] === '') {
            throw new AuthenticationException(sprintf('Invalid key "%s" in the token payload.', $idClaim));
        }

        return $payload[$idClaim];
    }
}
