<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Bundle\Controller;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class TestController
{
    public function securedAction(UserInterface $user)
    {
        return new JsonResponse([
            'class' => get_class($user),
            'roles' => $user->getRoles(),
            'username' => method_exists($user, 'getUserIdentifier') ? $user->getUserIdentifier() : $user->getUsername(),
        ]);
    }

    public function logoutAction()
    {
        throw new \Exception('This should never be reached!');
    }

    public function logoutCustomAction(Request $request, EventDispatcherInterface $eventDispatcher, TokenStorageInterface $tokenStorage)
    {
        $eventDispatcher->dispatch(new LogoutEvent($request, $tokenStorage->getToken()));

        return new Response();
    }
}
