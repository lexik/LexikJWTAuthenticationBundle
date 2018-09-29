<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Bundle\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Tests\Stubs\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class TestController
{
    public function securedAction(UserInterface $user)
    {
        return new JsonResponse([
            'class'    => get_class($user),
            'roles'    => $user->getRoles(),
            'username' => $user->getUsername(),
        ]);
    }
}
