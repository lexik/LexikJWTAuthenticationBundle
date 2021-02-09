<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token;

use Symfony\Component\Security\Guard\Token\GuardTokenInterface;

interface PreAuthenticationJWTUserTokenInterface extends GuardTokenInterface
{

    /**
     * @param array $payload
     * @return void
     */
    public function setPayload(array $payload);

    /**
     * @return mixed
     */
    public function getPayload();
}
