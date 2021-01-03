<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Bundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;

class TestController
{
    public function securedAction(UserInterface $user)
    {
        return new JsonResponse([
            'class' => get_class($user),
            'roles' => $user->getRoles(),
            'username' => $user->getUsername(),
        ]);
    }
}
