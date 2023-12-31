Invalidate token
================

The token blocklist relies on the ``jti`` claim, a standard claim designed for tracking and revoking JWTs. `"jti" (JWT ID) Claim <https://datatracker.ietf.org/doc/html/rfc7519#section-4.1.7>`_

The blocklist storage utilizes a cache implementing ``Psr\Cache\CacheItemPoolInterface``. The cache stores the ``jti`` of the blocked token to the cache, and the cache item expires after the "exp" (expiration time) claim of the token

Configuration
~~~~~~~~~~~~~

To configure token blocklist, update your `lexik_jwt_authentication.yaml` file:

.. code-block:: yaml

    # config/packages/lexik_jwt_authentication.yaml
    # ...
    lexik_jwt_authentication:
    # ...
        # invalidate the token on logout by storing it in the cache
        blocklist_token:
            enabled: true
            cache: cache.app


Enabling ``blocklist_token`` causes the activation of listeners:

* an event listener ``Lexik\Bundle\JWTAuthenticationBundle\EventListenerAddClaimsToJWTListener`` which adds a ``jti`` claim if not present when the token is created

* an event listener ``Lexik\Bundle\JWTAuthenticationBundle\BlockJWTListener`` which blocks JWTs on logout (``Symfony\Component\Security\Http\Event\LogoutEvent``)
or on login failure due to the user not being enabled (``Symfony\Component\Security\Core\Exception\DisabledException``)

* an event listener ``Lexik\Bundle\JWTAuthenticationBundle\RejectBlockedTokenListener`` which rejects blocked tokens during authentication

To block JWTs on logout, you must either activate logout in the firewall configuration or do it programmatically

* by firewall configuration

    .. code-block:: yaml
        # config/packages/security.yaml
        security:
            enable_authenticator_manager: true
            firewalls:
                api:
                    ...
                    jwt: ~
                    logout:
                        path: app_logout

* programmatically in a controller action

    .. code-block:: php
        use Symfony\Component\EventDispatcher\EventDispatcherInterface;
        use Symfony\Component\HttpFoundation\JsonResponse;
        use Symfony\Component\HttpFoundation\Request;
        use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
        use Symfony\Component\Security\Http\Event\LogoutEvent;
        //...
        class SecurityController
        {
            //...
            public function logout(Request $request, EventDispatcherInterface $eventDispatcher, TokenStorageInterface $tokenStorage)
            {
                $eventDispatcher->dispatch(new LogoutEvent($request, $tokenStorage->getToken()));

                return new JsonResponse();
            }
        ]

Refer to `Symfony logging out <https://symfony.com/doc/current/security.html#logging-out>`_  for more details.

Changing blocklist storage
~~~~~~~~~~~~~~~~~~~~~~~~~~

To change the blocklist storage, refer to `Configuring Cache with FrameworkBundle <https://symfony.com/doc/current/cache.html#configuring-cache-with-frameworkbundle>`_

.. code-block:: yaml

    # config/packages/framework.yaml
    framework:
        # ...
        cache:
            default_redis_provider: 'redis://localhost'
            pools:
                block_list_token_cache_pool:
                    adapter: cache.adapter.redis
        # ...
        blocklist_token:
            enabled: true
            cache: block_list_token_cache_pool
