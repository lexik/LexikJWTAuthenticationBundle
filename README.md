LexikJWTAuthenticationBundle
============================

This Symfony2 bundle provides JWT (Json Web Token) services to authenticate users against your application using the `namshi/jose` library.
Typical use case is a Symfony2 API with a (or several) Single Page App (angularJS, ember.js... or a mobile application).

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

`config.yml`

    lexik_jwt_authentication:
        private_key_path:   %private_key_path%
        public_key_path:    %public_key_path%
        pass_phrase:        %pass_phrase%
        token_ttl:          %token_ttl%

Usage
-----

`security.yml` example

    firewalls:
        dev:
            pattern:  ^/api/(_(profiler|wdt|doc))/
            security: false

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
                success_handler: lexik_jwt_authentication.handler.authentication_success
                failure_handler: lexik_jwt_authentication.handler.authentication_failure

        # main firewall, where user is authticated by its jwt token
        # (configure your client to send the token as an authorization header on each request on this firewall)
        api:
            pattern:  ^/api
            stateless: true
            simple_preauth:
                authenticator: lexik_jwt_authentication.jwt_authenticator

    access_control:
        - { path: ^/api/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/api, roles: ROLE_USER }

TODO
----

* Add functionnal tests usage doc
* Add a sample listener to add data to the authentication success response
* Add the authorization header key to config instead of joker ?
