Getting started
===============

Prerequisites
-------------

This bundle requires Symfony 3.4+ and the openssl extension.

**Protip:** Though the bundle doesn't enforce you to do so, it is highly recommended to use HTTPS. 

Installation
------------

Add [`lexik/jwt-authentication-bundle`](https://packagist.org/packages/lexik/jwt-authentication-bundle)
to your `composer.json` file:

    php composer.phar require "lexik/jwt-authentication-bundle"

#### Register the bundle: 

**Symfony 3 Version:**  
Register bundle into `app/AppKernel.php`:

``` php
public function registerBundles()
{
    return array(
        // ...
        new Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle(),
    );
}
```
**Symfony 4 Version :**   
Register bundle into `config/bundles.php` (Flex did it automatically):  
```php 
return [
    //...
    Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle::class => ['all' => true],
];
```

#### Generate the SSH keys:

``` bash
$ mkdir -p config/jwt
$ openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
$ openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```

Configuration
-------------

Configure the SSH keys path in your `config/packages/lexik_jwt_authentication.yaml` :

``` yaml
lexik_jwt_authentication:
    secret_key:       '%kernel.project_dir%/config/jwt/private.pem' # required for token creation
    public_key:       '%kernel.project_dir%/config/jwt/public.pem'  # required for token verification
    pass_phrase:      'your_secret_passphrase' # required for token creation, usage of an environment variable is recommended
    token_ttl:        3600
```

Configure your `config/packages/security.yaml` :

``` yaml
security:
    # ...
    
    firewalls:

        login:
            pattern:  ^/api/login
            stateless: true
            anonymous: true
            json_login:
                check_path:               /api/login_check
                success_handler:          lexik_jwt_authentication.handler.authentication_success
                failure_handler:          lexik_jwt_authentication.handler.authentication_failure

        api:
            pattern:   ^/api
            stateless: true
            guard:
                authenticators:
                    - lexik_jwt_authentication.jwt_token_authenticator

    access_control:
        - { path: ^/api/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/api,       roles: IS_AUTHENTICATED_FULLY }
```

Configure your routing into `config/routes.yaml` :

``` yaml
api_login_check:
    path: /api/login_check
```

Usage
-----

### 1. Obtain the token

The first step is to authenticate the user using its credentials.

You can test getting the token with a simple curl command like this (adapt host and port):
```bash
curl -X POST -H "Content-Type: application/json" http://localhost/api/login_check -d '{"username":"johndoe","password":"test"}'
```

If it works, you will receive something like this:

```json
{
   "token" : "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXUyJ9.eyJleHAiOjE0MzQ3Mjc1MzYsInVzZXJuYW1lIjoia29ybGVvbiIsImlhdCI6IjE0MzQ2NDExMzYifQ.nh0L_wuJy6ZKIQWh6OrW5hdLkviTs1_bau2GqYdDCB0Yqy_RplkFghsuqMpsFls8zKEErdX5TYCOR7muX0aQvQxGQ4mpBkvMDhJ4-pE4ct2obeMTr_s4X8nC00rBYPofrOONUOR4utbzvbd4d2xT_tj4TdR_0tsr91Y7VskCRFnoXAnNT-qQb7ci7HIBTbutb9zVStOFejrb4aLbr7Fl4byeIEYgp2Gd7gY"
}
```

Store it (client side), the JWT is reusable until its ttl has expired (3600 seconds by default).

### 2. Use the token

Simply pass the JWT on each request to the protected firewall, either as an authorization header
or as a query parameter. 

By default only the authorization header mode is enabled : `Authorization: Bearer {token}`

See [configuration reference](1-configuration-reference.md) document to enable query string parameter mode or change the header value prefix.

#### Examples

See [Functionally testing a JWT protected api](3-functional-testing.md) document
or the sandbox application ([Symfony2](https://github.com/slashfan/LexikJWTAuthenticationBundleSandbox) or [Symfony4](https://github.com/chalasr/lexik-jwt-authentication-sandbox)) for a fully working example.

Notes
-----

#### About token expiration

Each request after token expiration will result in a 401 response.
Redo the authentication process to obtain a new token. 

Maybe you want to use a **refresh token** to renew your JWT. In this case you can check [JWTRefreshTokenBundle](https://github.com/gesdinet/JWTRefreshTokenBundle).

#### Working with CORS requests

This is more of a Symfony2 related topic, but see [Working with CORS requests](4-cors-requests.md) document
to get a quick explanation on handling CORS requests.

#### A stateless form_login replacement

Using form_login security factory is very straightforward but it involves cookies exchange, even if the stateless parameter is set to true.

This may not be a problem depending on the system that makes calls to your API (like a typical SPA). But if it is, take a look at the [GfreeauGetJWTBundle](https://github.com/gfreeau/GfreeauGetJWTBundle), which provides a stateless replacement for form_login.

#### Impersonation

For impersonating users using JWT, see https://symfony.com/doc/current/security/impersonating_user.html 

#### Important note for Apache users

As stated in [this link](http://stackoverflow.com/questions/11990388/request-headers-bag-is-missing-authorization-header-in-symfony-2) and [this one](http://stackoverflow.com/questions/19443718/symfony-2-3-getrequest-headers-not-showing-authorization-bearer-token/19445020), Apache server will strip any `Authorization header` not in a valid HTTP BASIC AUTH format. 

If you intend to use the authorization header mode of this bundle (and you should), please add those rules to your VirtualHost configuration :

```apache
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
```

Further documentation
---------------------

The following documents are available:

- [Configuration reference](1-configuration-reference.md)
- [Data customization and validation](2-data-customization.md)
- [Functionally testing a JWT protected api](3-functional-testing.md)
- [Working with CORS requests](4-cors-requests.md)
- [JWT encoder service customization](5-encoder-service.md)
- [Extending JWTTokenAuthenticator](6-extending-jwt-authenticator.md)
- [Creating JWT tokens programmatically](7-manual-token-creation.md)
- [A database-less user provider](8-jwt-user-provider.md)
