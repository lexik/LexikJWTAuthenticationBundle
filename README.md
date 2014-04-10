LexikJWTAuthenticationBundle
============================

This bundle provides JWT (Json Web Token) services to authenticate users against your Symfony2 application using the great [namshi/jose](https://github.com/namshi/jose) library.

A typical use case for this would be a single page app (AngularJS, Ember.js, mobile app) with a Symfony2 API backend. 

Please note that this bundle is currently only compatible >= sf2.4, but a PR for sf2.3 is welcome.

Installation
------------

Installation with composer:

``` json
    ...
    "require": {
        ...
        "lexik/jwt-authentication-bundle": "dev-master",
        ...
    },
    ...
```

Next, be sure to enable the bundle in your `app/AppKernel.php` file:

``` php
public function registerBundles()
{
    return array(
        // ...
        new Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle(),
        // ...
    );
}
```

Configuration
-------------

Please read the namshi/jose library first.

Generate the keys used by the namshi/jose library to generate the token :

    $ openssl genrsa -out app/var/jwt/private.pem -aes256 4096
    $ openssl rsa -pubout -in app/var/jwt/private.pem -out app/var/jwt/public.pem

Then in your `config.yml` :

    lexik_jwt_authentication:
        private_key_path:   'app/var/jwt/private.pem'   # path to the private key
        public_key_path:    'app/var/jwt/public.pem'    # path to the public key
        pass_phrase:        ''                          # pass phrase, defaults to ''
        token_ttl:          86400                       # token ttl in seconds, defaults to 86400

Usage
-----

Example of possible `security.yml` :

    firewalls:
        # used to authenticate the user the first time with its username and password, using form login or http basic
        login:
            pattern:  ^/api/login
            stateless: true
            anonymous: true
            form_login:
                check_path: /api/login_check
                require_previous_session: false
                username_parameter: username
                password_parameter: password
                success_handler: lexik_jwt_authentication.handler.authentication_success # sends the token with some extra data on authentication success
                failure_handler: lexik_jwt_authentication.handler.authentication_failure

        # main firewall, where user will be authenticated by its jwt token (usually as an authorization header)
        api:
            pattern:  ^/api
            stateless: true
            simple_preauth:
                authenticator: lexik_jwt_authentication.jwt_authenticator

    access_control:
        - { path: ^/api/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/api, roles: ROLE_USER }

TODO (documentation)
--------------------

* Add functionnal tests usage doc
* Add a sample listener to add data to the authentication success response

TODO (code)
-----------

* Add the authorization header key to config instead of joker ?
