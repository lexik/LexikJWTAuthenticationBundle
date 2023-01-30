Getting started
===============

Prerequisites
-------------

This bundle requires Symfony 4.4+ and the openssl extension.

**Protip:** Though the bundle doesn't enforce you to do so, it is highly
recommended to use HTTPS.

Installation
------------

Add
`lexik/jwt-authentication-bundle <https://packagist.org/packages/lexik/jwt-authentication-bundle>`__
to your ``composer.json`` file:

.. code-block:: terminal

    $ php composer.phar require "lexik/jwt-authentication-bundle"

Register the bundle
~~~~~~~~~~~~~~~~~~~

Register bundle into ``config/bundles.php`` (Flex did it automatically):

.. code-block:: php

   return [
       //...
       Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle::class => ['all' => true],
   ];

Generate the SSL keys
~~~~~~~~~~~~~~~~~~~~~

.. code-block:: terminal

    $ php bin/console lexik:jwt:generate-keypair

Your keys will land in ``config/jwt/private.pem`` and
``config/jwt/public.pem`` (unless you configured a different path).

Available options:

-  ``--skip-if-exists`` will silently do nothing if keys already exist.
-  ``--overwrite`` will overwrite your keys if they already exist.

Otherwise, an error will be raised to prevent you from overwriting your
keys accidentally.

Configuration
-------------

Configure the SSL keys path and passphrase in your ``.env``:

.. code-block:: bash

   JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
   JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
   JWT_PASSPHRASE=

.. code-block:: yaml

   # config/packages/lexik_jwt_authentication.yaml
   lexik_jwt_authentication:
       secret_key: '%env(resolve:JWT_SECRET_KEY)%' # required for token creation
       public_key: '%env(resolve:JWT_PUBLIC_KEY)%' # required for token verification
       pass_phrase: '%env(JWT_PASSPHRASE)%' # required for token creation
       token_ttl: 3600 # in seconds, default is 3600

Configure application security
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. caution::

    Make sure the firewall ``login`` is place before ``api``, and if
    ``main`` exists, put it after ``api``, otherwise you will encounter
    ``/api/login_check`` route not found.

Symfony versions prior to 5.3
.............................

.. code-block:: yaml

    # config/packages/security.yaml
    security:
        # ...

        firewalls:
            login:
                pattern: ^/api/login
                stateless: true
                json_login:
                    check_path: /api/login_check # or api_login_check as defined in config/routes.yaml
                    success_handler: lexik_jwt_authentication.handler.authentication_success
                    failure_handler: lexik_jwt_authentication.handler.authentication_failure

            api:
                pattern:   ^/api
                stateless: true
                guard:
                    authenticators:
                        - lexik_jwt_authentication.jwt_token_authenticator

        access_control:
            - { path: ^/api/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
            - { path: ^/api,       roles: IS_AUTHENTICATED_FULLY }

Symfony 5.3 and higher
......................

.. code-block:: yaml

    # config/packages/security.yaml
    security:
        enable_authenticator_manager: true
        # ...

        firewalls:
            login:
                pattern: ^/api/login
                stateless: true
                json_login:
                    check_path: /api/login_check
                    success_handler: lexik_jwt_authentication.handler.authentication_success
                    failure_handler: lexik_jwt_authentication.handler.authentication_failure

            api:
                pattern:   ^/api
                stateless: true
                jwt: ~

        access_control:
            - { path: ^/api/login, roles: PUBLIC_ACCESS }
            - { path: ^/api,       roles: IS_AUTHENTICATED_FULLY }

Configure application routing
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: yaml

    # config/routes.yaml
    api_login_check:
        path: /api/login_check

Enable API Platform compatibility
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To enable the `API Platform <https://api-platform.com/>`__ compatibility, add the
``lexik_jwt_authentication.api_platform.check_path`` configuration option as following:

.. code-block:: yaml

   # config/packages/lexik_jwt_authentication.yaml
   lexik_jwt_authentication:
       # ...
       api_platform:
           check_path: /api/login_check

Usage
-----

.. _1-obtain-the-token:

1. Obtain the token
~~~~~~~~~~~~~~~~~~~

The first step is to authenticate the user using its credentials.
You can test getting the token with a simple curl command like this
(adapt host and port):

Linux or macOS:

.. code-block:: terminal

    $ curl -X POST -H "Content-Type: application/json" https://localhost/api/login_check -d '{"username":"johndoe","password":"test"}'

Windows:

.. code-block:: bash

    C:\> curl -X POST -H "Content-Type: application/json" https://localhost/api/login_check --data {\"username\":\"johndoe\",\"password\":\"test\"}

If it works, you will receive something like this:

.. code-block:: json

    {
        "token" : "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXUyJ9.eyJleHAiOjE0MzQ3Mjc1MzYsInVzZXJuYW1lIjoia29ybGVvbiIsImlhdCI6IjE0MzQ2NDExMzYifQ.nh0L_wuJy6ZKIQWh6OrW5hdLkviTs1_bau2GqYdDCB0Yqy_RplkFghsuqMpsFls8zKEErdX5TYCOR7muX0aQvQxGQ4mpBkvMDhJ4-pE4ct2obeMTr_s4X8nC00rBYPofrOONUOR4utbzvbd4d2xT_tj4TdR_0tsr91Y7VskCRFnoXAnNT-qQb7ci7HIBTbutb9zVStOFejrb4aLbr7Fl4byeIEYgp2Gd7gY"
    }

Store it (client side), the JWT is reusable until its TTL has expired
(3600 seconds by default).

.. _2-use-the-token:

2. Use the token
~~~~~~~~~~~~~~~~

Simply pass the JWT on each request to the protected firewall, either as
an authorization header or as a query parameter.

By default only the authorization header mode is enabled :
``Authorization: Bearer {token}``

See the :doc:`configuration reference </1-configuration-reference>` document
to enable query string parameter mode or change the header value prefix.

Examples
~~~~~~~~

See :doc:`Functionally testing a JWT protected
api </3-functional-testing>` document or the sandbox application
`Symfony4 <https://github.com/chalasr/lexik-jwt-authentication-sandbox>`__)
for a fully working example.

Notes
-----

About token expiration
~~~~~~~~~~~~~~~~~~~~~~

Each request after token expiration will result in a 401 response. Redo
the authentication process to obtain a new token.

Maybe you want to use a **refresh token** to renew your JWT. In this
case you can check
`JWTRefreshTokenBundle <https://github.com/markitosgv/JWTRefreshTokenBundle>`__.

Working with CORS requests
~~~~~~~~~~~~~~~~~~~~~~~~~~

This is more of a Symfony2 related topic, but see :doc:`Working with CORS requests </4-cors-requests>`
document to get a quick explanation on handling CORS requests.

Impersonation
~~~~~~~~~~~~~

For impersonating users using JWT, see
https://symfony.com/doc/current/security/impersonating_user.html

Important note for Apache users
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

As stated in `this
link <https://stackoverflow.com/questions/11990388/request-headers-bag-is-missing-authorization-header-in-symfony-2>`__
and `this
one <https://stackoverflow.com/questions/19443718/symfony-2-3-getrequest-headers-not-showing-authorization-bearer-token/19445020>`__,
Apache server will strip any ``Authorization header`` not in a valid
HTTP BASIC AUTH format.

If you intend to use the authorization header mode of this bundle (and
you should), please add those rules to your VirtualHost configuration :

.. code-block:: apache

    SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1

Further documentation
---------------------

The following documents are available:

-  :doc:`Configuration reference </1-configuration-reference>`
-  :doc:`Data customization and validation </2-data-customization>`
-  :doc:`Functionally testing a JWT protected api </3-functional-testing>`
-  :doc:`Working with CORS requests </4-cors-requests>`
-  :doc:`JWT encoder service customization </5-encoder-service>`
-  :doc:`Extending Authenticator </6-extending-jwt-authenticator>`
-  :doc:`Creating JWT tokens programmatically </7-manual-token-creation>`
-  :doc:`A database-less user provider </8-jwt-user-provider>`
-  :doc:`Accessing the authenticated JWT token </9-access-authenticated-jwt-token>`
