Configuration reference
=======================

Bundle configuration
--------------------

Minimal configuration
~~~~~~~~~~~~~~~~~~~~~

Using RSA/ECDSA
~~~~~~~~~~~~~~~

.. code-block:: yaml

    # config/packages/lexik_jwt_authentication.yaml
    #...
    lexik_jwt_authentication:
        secret_key: '%kernel.project_dir%/config/jwt/private.pem' # path to the secret key OR raw secret key, required for creating tokens
        public_key: '%kernel.project_dir%/config/jwt/public.pem'  # path to the public key OR raw public key, required for verifying tokens
        pass_phrase: 'yourpassphrase' # required for creating tokens
        # Additional public keys are used to verify signature of incoming tokens, if the key provided in "public_key" configuration node doesn't verify the token
        additional_public_keys:
            - '%kernel.project_dir%/config/jwt/public1.pem'
            - '%kernel.project_dir%/config/jwt/public2.pem'
            - '%kernel.project_dir%/config/jwt/public3.pem'

Using HMAC
~~~~~~~~~~

.. code-block:: yaml

    # config/packages/lexik_jwt_authentication.yaml
    #...
    lexik_jwt_authentication:
        secret_key: yoursecret

Full default configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: yaml

    # config/packages/lexik_jwt_authentication.yaml
    # ...
    lexik_jwt_authentication:
        secret_key: ~
        public_key: ~
        pass_phrase: ~
        token_ttl: 3600 # token TTL in seconds, defaults to 1 hour
        user_identity_field: username # key under which the user identity will be stored in the token payload
        clock_skew: 0
        allow_no_expiration: false # set to true to allow tokens without exp claim

        # token encoding/decoding settings
        encoder:
            # token encoder/decoder service - default implementation based on the lcobucci/jwt library
            service:            lexik_jwt_authentication.encoder.lcobucci

            # encryption algorithm used by the encoder service
            signature_algorithm: RS256

        # token extraction settings
        token_extractors:
            # look for a token as Authorization Header
            authorization_header:
                enabled: true
                prefix:  Bearer
                name:    Authorization

            # check token in a cookie
            cookie:
                enabled: false
                name:    BEARER

            # check token in query string parameter
            query_parameter:
                enabled: false
                name:    bearer

            # check token in a cookie
            split_cookie:
                enabled: false
                cookies:
                    - jwt_hp
                    - jwt_s

        # remove the token from the response body when using cookies
        remove_token_from_body_when_cookies_used: true

        # invalidate the token on logout by storing it in the cache
        blocklist_token:
            enabled: true
            cache: cache.app

Encoder configuration
~~~~~~~~~~~~~~~~~~~~~

service
.......

Defaults to ``lexik_jwt_authentication.encoder.lcobucci`` which is based
on the `Lcobucci/JWT <https://github.com/lcobucci/jwt>`__ library.

For an advanced token encoding with higher encryption support, please
see the
`Spomky-Labs/lexik-jose-bridge <https://github.com/Spomky-Labs/lexik-jose-bridge>`__
which is based on the great
`web-token/jwt-framework <https://github.com/web-token/jwt-framework>`__
library.

To create your own encoder service, see the
:doc:`JWT encoder service customization chapter </5-encoder-service>`.

signature_algorithm
...................

One of the algorithms supported by the default encoder for the
configured `crypto engine <#crypto_engine>`__.

-  HS256, HS384, HS512 (HMAC)
-  RS256, RS384, RS512 (RSA)
-  ES256, ES384, ES512 (ECDSA)

Automatically generating cookies
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You are now able to automatically generate secure and httpOnly cookies
when the cookie token extractor is enabled
`#753 <https://github.com/lexik/LexikJWTAuthenticationBundle/pull/753>`__.

.. code-block:: yaml

    token_extractors:
        cookie:
            enabled: true
            name: BEARER
    # ...
    set_cookies:
        BEARER: ~

    # Full config with defaults:
    #  BEARER:
    #      lifetime: null (defaults to token ttl)
    #      samesite: lax
    #      path: /
    #      domain: null (null means automatically set by symfony)
    #      secure: true (default to true)
    #      httpOnly: true
    #      partitioned: false

Automatically generating split cookies
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You are also able to automatically generate split cookies. Benefits of
this approach are in
`this post <https://medium.com/lightrail/getting-token-authentication-right-in-a-stateless-single-page-application-57d0c6474e3>`__.

Set the signature cookie (jwt_s) lifetime to 0 to create session
cookies.

Keep in mind, that SameSite attribute is **not supported** in
`some browsers <https://caniuse.com/#feat=same-site-cookie-attribute>`__

.. code-block:: yaml

    token_extractors:
        split_cookie:
            enabled: true
            cookies:
                - jwt_hp
                - jwt_s

    set_cookies:
        jwt_hp:
            lifetime: null
            samesite: strict
            path: /
            domain: null
            httpOnly: false
            partitioned: false # Only for Symfony 6.4 or higher
            split:
                - header
                - payload

        jwt_s:
            lifetime: 0
            samesite: strict
            path: /
            domain: null
            httpOnly: true
            partitioned: false # Only for Symfony 6.4 or higher
            split:
                - signature

Keep the token in the body when using cookies
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When using cookies the response defaults to an empty body and result
code 204. It is possible to modify this behaviour.

Keep in mind, this invalidates a requirement from the
`previously mentioned post <https://medium.com/lightrail/getting-token-authentication-right-in-a-stateless-single-page-application-57d0c6474e3>`__,
namely "JavaScript/front-end should never have access to the full JWT".

.. code-block:: yaml

    remove_token_from_body_when_cookies_used: false

Security configuration
----------------------

For Symfony 5.3 and higher, use the ``jwt`` authenticator:

.. code-block:: yaml

    # config/packages/security.yaml
    security:
        enable_authenticator_manager: true
        firewalls:
            api:
                # ...
                jwt: ~ # enables the jwt authenticator

            # Full config with defaults:
            #    jwt:
            #        provider: null  (you can put provider here or just ignore this config)
            #        authenticator: lexik_jwt_authentication.security.jwt_authenticator (default jwt authenticator)
            # ...

For Symfony versions prior to 5.3, use the Guard authenticator:

.. code-block:: yaml

    firewalls:
        # ...
        api:
            # ...
            guard:
                authenticators:
                    - 'lexik_jwt_authentication.jwt_token_authenticator'

Authenticator
.............

For more details about using custom authenticator in your application,
see :doc:`Extending JWT Authenticator </6-extending-jwt-authenticator>`.

Database-less User Provider
...........................

For a database-less authentication (i.e. trusting into the JWT data
instead of reloading the user from the database), see
:doc:`"A database less user provider" </8-jwt-user-provider>`.
