<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * JWTRolesDefinedEvent.
 */
class JWTRolesDefinedEvent extends Event
{
    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @var array
     */
    protected $payload;

    /**
     * @var array
     */
    protected $roles;

    /**
     * JWTRolesDefinedEvent constructor.
     * @param UserInterface $user
     * @param array $payload
     */
    public function __construct(UserInterface $user, array $payload)
    {
        $this->payload = $payload;
        $this->user   = $user;
        $this->roles = $user->getRoles();
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     * @return JWTRolesDefinedEvent
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
        return $this;
    }
}
