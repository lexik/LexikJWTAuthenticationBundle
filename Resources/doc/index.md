Getting started
===============

Prerequisites
-------------

This bundle requires Symfony 2.3+ and the OpenSSL library.

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
$ openssl genrsa -out app/var/jwt/private.pem -aes256 4096
$ openssl rsa -pubout -in app/var/jwt/private.pem -out app/var/jwt/public.pem
```

Configuration
-------------

Configure the SSH keys path in your `config.yml` :

``` yaml
lexik_jwt_authentication:
    private_key_path: %kernel.root_dir%/var/jwt/private.pem   # ssh private key path
    public_key_path:  %kernel.root_dir%/var/jwt/public.pem    # ssh public key path
    pass_phrase:      ''                                      # ssh key pass phrase
    token_ttl:        86400                                   # token ttl - defaults to 86400
```

Confgure your `security.yml` :

``` yaml
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
        lexik_jwt: ~

access_control:
    - { path: ^/api/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
    - { path: ^/api,       roles: IS_AUTHENTICATED_FULLY }
```

Usage
-----

### 1. Obtain the token

The first step is to authenticate the user using its credentials.
A classical form_login on an anonymously accessible firewall will do perfect.

Just set the provided `lexik_jwt_authentication.handler.authentication_success` service as success handler to
generate the token and send it as part of a json response body.

Store it (client side), the JWT is reusable until its ttl has expired (86400 seconds by default).

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

#### Working with CORS requests

This is more of a Symfony2 related topic, but see [Working with CORS requests](4-cors-requests.md) document
to get a quick explanation on handling CORS requests.

#### A stateless form_login replacement

Using form_login security factory is very straightforward but it involves cookies exchange, even if the stateless parameter is set to true.

This may not be a problem depending on the system that makes calls to your API (like a typical SPA). But if it is, take a look at the [GfreeauGetJWTBundle](https://github.com/gfreeau/GfreeauGetJWTBundle), which provides a stateless replacement for form_login.

Further documentation
---------------------

The following documents are available:

- [Configuration reference](1-configuration-reference.md)
- [Data customization and validation](2-data-customization.md)
- [Functionally testing a JWT protected api](3-functional-testing.md)
- [Working with CORS requests](4-cors-requests.md)
