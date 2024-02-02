<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * AuthenticationSuccessEvent.
 *
 * @author Dev Lexik <dev@lexik.fr>
 */
class AuthenticationSuccessEvent extends Event
{
    protected array $data;
    protected UserInterface $user;
    protected Response $response;

    public function __construct(array $data, UserInterface $user, Response $response)
    {
        $this->data = $data;
        $this->user = $user;
        $this->response = $response;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}
