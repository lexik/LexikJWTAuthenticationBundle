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
 *
 * @method string createFromPayload(UserInterface $user, array $payload = []);
 * @method array parse(string $token) Parses a raw JWT token and returns its payload
 */
interface JWTTokenManagerInterface
{
    /**
     * @return string The JWT token
     */
    public function create(UserInterface $user);

    /**
     * @return array|false The JWT token payload or false if an error occurs
     * @throws JWTDecodeFailureException
     */
    public function decode(TokenInterface $token);

    /**
     * Sets the field used as identifier to load an user from a JWT payload.
     *
     * @param string $field
     */
    public function setUserIdentityField($field);

    /**
     * Returns the field used as identifier to load an user from a JWT payload.
     *
     * @return string
     */
    public function getUserIdentityField();

    /**
     * Returns the claim used as identifier to load an user from a JWT payload.
     *
     * @return string
     */
    public function getUserIdClaim();
}
