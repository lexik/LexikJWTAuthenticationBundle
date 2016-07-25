<?php


namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * JWTCreatedEvent
 */
class JWTCreatedEvent extends Event
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @var Request
     *
     * @deprecated since 1.7, removed in 2.0
     */
    protected $request;

    /**
     * @param array         $data
     * @param UserInterface $user
     * @param Request|null  $request Deprecated
     */
    public function __construct(array $data, UserInterface $user, Request $request = null)
    {
        if (null !== $request && class_exists('Symfony\Component\HttpFoundation\RequestStack')) {
            @trigger_error(sprintf('Passing a Request instance as first argument of %s() is deprecated since version 1.7 and will be removed in 2.0.%sInject the "@request_stack" service in your event listener instead.', __METHOD__, PHP_EOL), E_USER_DEPRECATED);

            $this->request = $request;
        }

        $this->data = $data;
        $this->user = $user;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @deprecated since 1.7, removed in 2.0
     *
     * @return Request
     */
    public function getRequest()
    {
        if (class_exists('Symfony\Component\HttpFoundation\RequestStack')) {
            @trigger_error(sprintf('Method %s() is deprecated since version 1.7 and will be removed in 2.0.%sUse  Symfony\Component\HttpFoundation\RequestStack::getCurrentRequest() instead.', __METHOD__, PHP_EOL), E_USER_DEPRECATED);
        }

        return $this->request;
    }
}
