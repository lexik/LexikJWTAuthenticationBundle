<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class TestController extends Controller
{
    public function securedAction()
    {
        return new JsonResponse([
            'class'    => get_class($this->getUser()),
            'roles'    => $this->getUser()->getRoles(),
            'username' => $this->getUser()->getUsername(),
        ]);
    }

    public function loginCheckAction()
    {
        throw new \RuntimeException('loginCheckAction() should never be called directly.');
    }
}
