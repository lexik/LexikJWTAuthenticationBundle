Getting started
===============

Prerequisites
-------------

This bundle requires Symfony 2.8+ (and the OpenSSL library if you intend to use the default provided encoder).

**Protip:** Though the bundle doesn't enforce you to do so, it is highly recommended to use HTTPS. 

Installation
------------

Add [`lexik/jwt-authentication-bundle`](https://packagist.org/packages/lexik/jwt-authentication-bundle)
to your `composer.json` file:

    php composer.phar require "lexik/jwt-authentication-bundle"

Register the bundle in `app/AppKernel.php`:

``` php
public function registerBundles()
{
    return array(
        // ...
        new Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle(),
    );
}
```

Generate the SSH keys :

``` bash
$ mkdir -p var/jwt # For Symfony3+, no need of the -p option
$ openssl genrsa -out var/jwt/private.pem -aes256 4096
$ openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem
```

Configuration
-------------

Configure the SSH keys path in your `config.yml` :

``` yaml
lexik_jwt_authentication:
    private_key_path: '%jwt_private_key_path%'
    public_key_path:  '%jwt_public_key_path%'
    pass_phrase:      '%jwt_key_pass_phrase%'
    token_ttl:        '%jwt_token_ttl%'
```

Configure your `parameters.yml.dist` :

``` yaml
jwt_private_key_path: '%kernel.root_dir%/../var/jwt/private.pem' # ssh private key path
jwt_public_key_path:  '%kernel.root_dir%/../var/jwt/public.pem'  # ssh public key path
jwt_key_pass_phrase:  ''                                         # ssh key pass phrase
jwt_token_ttl:        3600
```

Configure your `security.yml` :

``` yaml
security:
    # ...
    
    firewalls:

        login:
            pattern:  ^/api/login
            stateless: true
            anonymous: true
            form_login:
                check_path:               /api/login_check
                success_handler:          lexik_jwt_authentication.handler.authentication_success
                failure_handler:          lexik_jwt_authentication.handler.authentication_failure
                require_previous_session: false

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

Configure your `routing.yml` :

``` yaml
api_login_check:
    path: /api/login_check
```

Usage
-----

### 1. Obtain the token

The first step is to authenticate the user using its credentials.
A classical form_login on an anonymously accessible firewall will do perfect.

Just set the provided `lexik_jwt_authentication.handler.authentication_success` service as success handler to
generate the token and send it as part of a json response body.

Store it (client side), the JWT is reusable until its ttl has expired (3600 seconds by default).

Note: You can test getting the token with a simple curl command like this:

```bash
curl -X POST http://localhost:8000/api/login_check -d _username=johndoe -d _password=test
```

If it works, you will receive something like this:

```json
{
   "token" : "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXUyJ9.eyJleHAiOjE0MzQ3Mjc1MzYsInVzZXJuYW1lIjoia29ybGVvbiIsImlhdCI6IjE0MzQ2NDExMzYifQ.nh0L_wuJy6ZKIQWh6OrW5hdLkviTs1_bau2GqYdDCB0Yqy_RplkFghsuqMpsFls8zKEErdX5TYCOR7muX0aQvQxGQ4mpBkvMDhJ4-pE4ct2obeMTr_s4X8nC00rBYPofrOONUOR4utbzvbd4d2xT_tj4TdR_0tsr91Y7VskCRFnoXAnNT-qQb7ci7HIBTbutb9zVStOFejrb4aLbr7Fl4byeIEYgp2Gd7gY"
}
```

### 2. Use the token

Simply pass the JWT on each request to the protected firewall, either as an authorization header
or as a query parameter. 

By default only the authorization header mode is enabled : `Authorization: Bearer {token}`

See [configuration reference](1-configuration-reference.md) document to enable query string parameter mode or change the header value prefix.

#### Examples

See [Functionally testing a JWT protected api](3-functional-testing.md) document
or the [sandbox application](https://github.com/slashfan/LexikJWTAuthenticationBundleSandbox) for a fully working example.

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

For impersonating users using JWT, see [lafourchette/SwitchUserStatelessBundle](https://github.com/lafourchette/SwitchUserStatelessBundle), a stateless replacement of the `switch_user` listener.

#### Important note for Apache users

As stated in [this link](http://stackoverflow.com/questions/11990388/request-headers-bag-is-missing-authorization-header-in-symfony-2) and [this one](http://stackoverflow.com/questions/19443718/symfony-2-3-getrequest-headers-not-showing-authorization-bearer-token/19445020), Apache server will strip any `Authorization header` not in a valid HTTP BASIC AUTH format. 

If you intend to use the authorization header mode of this bundle (and you should), please add those rules to your VirtualHost configuration :

```apache
RewriteEngine On
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]
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
