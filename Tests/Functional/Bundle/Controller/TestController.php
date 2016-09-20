<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class TestController extends Controller
{
    public function securedAction()
    {
        return new Response();
    }

    public function loginCheckAction()
    {
        throw new \RuntimeException('loginCheckAction() should never be called directly.');
    }
}
