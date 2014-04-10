LexikJWTAuthenticationBundle
============================

This bundle provides JWT (Json Web Token) services to authenticate users against your Symfony2 application using the great [namshi/jose](https://github.com/namshi/jose) library.

A typical use case for this would be a single page app (AngularJS, Ember.js, mobile app) using a Symfony2 API as backend. 

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

Please read the [namshi/jose library](https://github.com/namshi/jose) documentation first.

First, generate the keys used for token generation (for example) :

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

First of all, you need to authenticate the user using its credentials through form login or http basic. Set the `lexik_jwt_authentication.handler.authentication_success` service as success handler, which will generate the JWT token and send it as the body of a JsonResponse (along with some non-encrypted optionnal data, see example below).

Store the token in your client application (using cookie, localstorage or wathever - the token is encrypted). 

Now, you only need to pass it as an Authorization header on each future request. If it results in a 401 response, your token is invalid (most likely its ttl has expired - 86400 seconds by default). 

Redo the authentication process to get a fresh token.

### Example of possible `security.yml` :

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
                success_handler: lexik_jwt_authentication.handler.authentication_success # sends a 200 response with the token and optionnal extra data as body               
                failure_handler: lexik_jwt_authentication.handler.authentication_failure # sends a 401 response

        # protected firewall, where a user will be authenticated by its jwt token (passed as an authorization header)
        api:
            pattern:  ^/api
            stateless: true
            simple_preauth:
                authenticator: lexik_jwt_authentication.jwt_authenticator

    access_control:
        - { path: ^/api/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/api, roles: ROLE_USER }

### Add extra data to response (example)

If you need to send some extra data (not encrypted) to your client, let's say the user roles or name, you can do that by listenig to the lexik_jwt_authentication.on_authentication_success event.

For example :

In your `services.yml` :

    acme_user.event.authentication_success_listener:
        class: Acme\Bundle\UserBundle\EventListener\AuthenticationSuccessListener
        tags:
            - { name: kernel.event_listener, event: lexik_jwt_authentication.on_authentication_success, method: onAuthenticationSuccess }
            
In your `AuthenticationSuccessListener.php` :

    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof Acme\Bundle\UserBundle\Entity\User) {
            return;
        }

        $data['profile'] = [
            'firstname' => $user->getFirstName(),
            'lastname'  => $user->getLastName(),
            'roles'     => $user->getRoles(),
        ];

        $event->setData($data);
    }

### Functionnal tests (example)

Generate some test specific keys, for example :

    $ openssl genrsa -out app/cache/test/jwt/private.pem -aes256 4096
    $ openssl rsa -pubout -in app/cache/test/jwt/private.pem -out app/cache/test/jwt/public.pem

Override the bundle configuration in your `config_test.yml` :

    lexik_jwt_authentication:
        private_key_path:   %kernel.cache_dir%/jwt/private.pem
        public_key_path:    %kernel.cache_dir%/jwt/jwt/public.pem
        pass_phrase:        'test'
        
In your functionnal tests, create an authenticated client :

    protected function createAuthenticatedClient($username = 'admin@acme.tld')
    {
        $client = static::createClient();

        $jwt = $client->getContainer()->get('lexik_jwt_authentication.jwt_encoder')->encode([
            'username' => $username,
        ]);

        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $jwt->getTokenString()));

        return $client;
    }


TODO
----

* Add the authorization header key to config instead of joker ?
* Add the possibility to use the token as a query string parameter, or cookie...
