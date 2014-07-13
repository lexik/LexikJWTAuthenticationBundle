LexikJWTAuthenticationBundle
============================

[![Build Status](https://travis-ci.org/lexik/LexikJWTAuthenticationBundle.svg?branch=master)](https://travis-ci.org/lexik/LexikJWTAuthenticationBundle)
[![Latest Stable Version](https://poser.pugx.org/lexik/jwt-authentication-bundle/v/stable.svg)](https://packagist.org/packages/lexik/jwt-authentication-bundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/67573b6f-e182-4394-b26a-649c323784f6/mini.png)](https://insight.sensiolabs.com/projects/67573b6f-e182-4394-b26a-649c323784f6)


This bundle provides JWT (Json Web Token) authentication for your Symfony2 REST API using the great [`namshi/jose`](https://github.com/namshi/jose) library.

Installation
------------

Require [`lexik/jwt-authentication-bundle`](https://packagist.org/packages/lexik/jwt-authentication-bundle)
into your `composer.json` file:

``` json
{
    "require": {
        "lexik/jwt-authentication-bundle": "@stable"
    }
}
```

**Protip:** you should browse the
[`lexik/jwt-authentication-bundle`](https://packagist.org/packages/lexik/jwt-authentication-bundle)
page to choose a stable version to use, avoid the `@stable` meta constraint.

Register the bundle in `app/AppKernel.php`:

``` php
// app/AppKernel.php
public function registerBundles()
{
    return array(
        // ...
        new Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle(),
    );
}
```

Configuration
-------------

Please read the [namshi/jose library](https://github.com/namshi/jose) documentation first.

First, generate the keys used for token generation (for example) :

``` bash
$ openssl genrsa -out app/var/jwt/private.pem -aes256 4096
$ openssl rsa -pubout -in app/var/jwt/private.pem -out app/var/jwt/public.pem
```

Then in your `config.yml` :

``` yaml
lexik_jwt_authentication:
    private_key_path:   %kernel.root_dir%/var/jwt/private.pem   # path to the private key
    public_key_path:    %kernel.root_dir%/var/jwt/public.pem    # path to the public key
    pass_phrase:        ''                                      # optional - pass phrase, defaults to ''
    token_ttl:          86400                                   # optional - token ttl, defaults to 86400
```

Usage
-----

First of all, you need to authenticate the user using its credentials through form login.
Set the `lexik_jwt_authentication.handler.authentication_success` service as success handler, which will generate the JWT token and send it as the body of a JsonResponse.

Store the token in your client application (using cookie, localstorage or whatever - the token is encrypted).
Now, you only need to pass the token on each future request, either as an authorization header or as a query string parameter.

If it results in a 401 response, your token is invalid (most likely its ttl has expired - 86400 seconds by default).
Redo the authentication process to get a fresh token.

### Example of possible `security.yml` :

``` yaml
firewalls:
    # used to authenticate the user the first time with its username and password, using form login
    login:
        pattern:  ^/api/login
        stateless: true
        anonymous: true
        form_login:
            check_path: /api/login_check
            require_previous_session: false
            username_parameter: username
            password_parameter: password
            success_handler: lexik_jwt_authentication.handler.authentication_success # generate the jwt token and send it as 200 response body
            failure_handler: lexik_jwt_authentication.handler.authentication_failure # send a 401 response

    # protected firewall, where a user will be authenticated by its jwt token
    api:
        pattern:   ^/api
        stateless: true

        # default configuration
        lexik_jwt: ~ # check token in Authorization Header, with a value prefix of 'Bearer'

        # advanced configuration
        lexik_jwt:
            authorization_header: # check token in Authorization Header
                enabled: true
                prefix:  Bearer
            query_parameter:      # check token in query string parameter
                enabled: true
                name:    bearer
            throw_exceptions: false     # When an authentication failure occurs, return a 401 response immediately
            create_entry_point: true    # When no authentication details are provided, create a default entry point that returns a 401 response

access_control:
    - { path: ^/api/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
    - { path: ^/api, roles: ROLE_USER }
```

### Add extra data to response (example)

If you need to send some extra data (not encrypted) to your client, let's say the user roles or name, you can do that by listening to the lexik_jwt_authentication.on_authentication_success event.

For example :

In your `services.yml` :

``` yaml
    acme_user.event.authentication_success_listener:
        class: Acme\Bundle\UserBundle\EventListener\AuthenticationSuccessListener
        tags:
            - { name: kernel.event_listener, event: lexik_jwt_authentication.on_authentication_success, method: onAuthenticationSuccessResponse }
```

In your `AuthenticationSuccessListener.php` :

``` php
/**
 * @param AuthenticationSuccessEvent $event
 */
public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
{
    $data = $event->getData();
    $user = $event->getUser();

    if (!$user instanceof \Acme\Bundle\UserBundle\Entity\User) {
        return;
    }

    $data['profile'] = [
        'firstname' => $user->getFirstName(),
        'lastname'  => $user->getLastName(),
        'roles'     => $user->getRoles(),
    ];

    $event->setData($data);
}
```

### Using jwt authentication in functional tests

Generate some test specific keys, for example :

``` bash
$ openssl genrsa -out app/cache/test/jwt/private.pem -aes256 4096
$ openssl rsa -pubout -in app/cache/test/jwt/private.pem -out app/cache/test/jwt/public.pem
```

Override the bundle configuration in your `config_test.yml` :

``` yaml
lexik_jwt_authentication:
    private_key_path:   %kernel.cache_dir%/jwt/private.pem
    public_key_path:    %kernel.cache_dir%/jwt/jwt/public.pem
```

In your functional tests, create an authenticated client :

``` php
/**
 * Create a client with a default Authorization header.
 *
 * @param string $username
 * @param string $password
 *
 * @return \Symfony\Bundle\FrameworkBundle\Client
 */
protected function createAuthenticatedClient($username = 'user', $password = 'password')
{
    $client = static::createClient();
    $client->request(
        'POST',
        '/login_check',
        array(
            'username' => $username,
            'password' => $password,
        )
    );

    $response = $client->getResponse();
    $data     = json_decode($response->getContent(), true);

    $client = static::createClient();
    $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));

    return $client;
}
```

### Server configuration

Required by Symfony 2.3 or newer.

Add the following lines to the `.htaccess` right after `RewriteEngine On`:

```
    RewriteCond %{HTTP:Authorization} ^(.*)
    RewriteRule .* - [e=HTTP_Authorization:%1]
```
