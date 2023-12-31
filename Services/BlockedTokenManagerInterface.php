<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services;

use Lexik\Bundle\JWTAuthenticationBundle\Exception\MissingClaimException;

interface BlockedTokenManagerInterface
{
    /**
     * @throws MissingClaimException if required claims do not exist in the payload
     */
    public function add(array $payload): bool;

    /**
     * @throws MissingClaimException if required claims do not exist in the payload
     */
    public function has(array $payload): bool;

    /**
     * @throws MissingClaimException if required claims do not exist in the payload
     */
    public function remove(array $payload): void;
}
