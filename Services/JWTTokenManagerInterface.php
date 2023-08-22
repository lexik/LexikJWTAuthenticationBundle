<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services;

use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * JWTTokenManagerInterface must be implemented by classes able to create/decode
 * JWT tokens.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 * @author Eric Lannez <eric.lannez@gmail.com>
 */
interface JWTTokenManagerInterface
{
    /**
     * @return string The JWT token
     */
    public function create(UserInterface $user);

    public function createFromPayload(UserInterface $user, array $payload = []): string;

    /**
     * @return array|false The JWT token payload or false if an error occurs
     * @throws JWTDecodeFailureException
     */
    public function decode(TokenInterface $token);

    /**
     * Parses a raw JWT token and returns its payload
     */
    public function parse(string $token): array;

    /**
     * Returns the claim used as identifier to load an user from a JWT payload.
     *
     * @return string
     */
    public function getUserIdClaim();
}
