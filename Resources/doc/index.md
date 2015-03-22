Getting started
===============
This bundle requires Symfony 2.3+ (and the OpenSSL library if you intend to use the default provided encoder).

**Protip:** Though the bundle doesn't enforce you to do so, it is highly recommended to use HTTPS.

Installation
------------

1. Require [`lexik/jwt-authentication-bundle`](https://packagist.org/packages/lexik/jwt-authentication-bundle) into your `composer.json` file:

    ``` json
    {
        "require": {
            "lexik/jwt-authentication-bundle": "@stable"
        },
    }
    ```

    **Protip:** you should browse the [`lexik/jwt-authentication-bundle`](https://packagist.org/packages/lexik/jwt-authentication-bundle) page to choose a stable version to use, avoid the `@stable` meta constraint.

2. Register the bundle in `app/AppKernel.php`:

    ``` php
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

1. Configure the encoder service in your `config.yml`.

    ``` yaml
    services:
        lexik_jwt_encoder:
            class: Lexik\Bundle\JWTAuthenticationBundle\Encoder\SharedKeyJWTEncoder
            arguments: [ %shared_key_algorithm%, %shared_key% ]
    ```

    For more information about encoders, see [encoders documentation](5-encoder-service).

1. Configure the JWT authentication in your `config.yml`:

    ``` yaml
    lexik_jwt_authentication:
        token_ttl:       86400             # token ttl - defaults to 86400
        encoder_service: lexik_jwt_encoder # encoder/decoder service
    ```

1. Configure the firewalls in your `security.yml`:

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

1. Configure your `routing.yml`:

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
#### Important note for Apache users

As stated in [this link](http://stackoverflow.com/questions/11990388/request-headers-bag-is-missing-authorization-header-in-symfony-2) and [this one](http://stackoverflow.com/questions/19443718/symfony-2-3-getrequest-headers-not-showing-authorization-bearer-token/19445020), Apache server will strip any `Authorization header` not in a valid HTTP BASIC AUTH format.

If you intend to use the authorization header mode of this bundle (and you should), please add those rules to your VirtualHost configuration :

```apache
RewriteEngine On
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]
```

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
- [JWT encoder service customization](5-encoder-service.md)
