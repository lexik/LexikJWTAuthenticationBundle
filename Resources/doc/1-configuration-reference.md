Configuration reference
=======================

Configuration reference
------------------------

### Full default configuration

``` yaml
# app/config/config.yml
# ...
lexik_jwt_authentication:
    # ssh private key path
    private_key_path:    %kernel.root_dir%/var/jwt/private.pem     
    # ssh public key path
    public_key_path:     %kernel.root_dir%/var/jwt/public.pem
    # ssh key pass phrase
    pass_phrase:         ''
    # token ttl
    token_ttl:           86400
    # key under which the user identity will be stored in the token payload
    user_identity_field: username

    encoder:
        # token encoder/decoder service - default implementation based on the namshi/jose library
        service:               lexik_jwt_authentication.encoder.default
        # encryption engine used by the encoder service
        encryption_engine:     openssl
        # encryption algorithm used by the encoder service
        signature_algorithm:  RS256                                  
```

### Encoder configuration

#### service

Default based on the [Namshi/JOSE](https://github.com/namshi/jose) library.  
To create your own encoder service, see the [JWT encoder service customization chapter](5-encoder-service.md).

#### encryption_engine

One of `openssl` and `phpseclib`, the encryption engines supported by the default token encoder service.  
See the [OpenSSL](https://github.com/openssl/openssl) and [phpseclib](https://github.com/phpseclib/phpseclib) documentations for more information.

#### signature_algorithm

One of the algorithms supported by the default encoder for the configured [encryption engine](#encryption_engine).

__Supported algorithms for OpenSSL:__
- RS256, RS384, RS512 (RSA)
- ES256, ES384, ES512 (ECDSA)
- HS256, HS384, HS512 (HMAC)

__Supported algorithms for phpseclib:__
- RS256, RS384, RS512 (RSA)

Security reference
-------------------

### Simplest configuration

``` yaml
# app/config/security.yml
# ...
firewalls:
    # ...
    api:
        # ...
        lexik_jwt: ~ # check token in Authorization Header, with a value prefix of 'Bearer'
```

### Full default configuration

``` yaml
# app/config/security.yml
# ...
firewalls:
    # ...
    api:
        # ...
        # advanced configuration
        lexik_jwt:
            authorization_header: # check token in Authorization Header
                enabled: true
                prefix:  Bearer
                name:    Authorization
            cookie:               # check token in a cookie
                enabled: false
                name:    BEARER
            query_parameter:      # check token in query string parameter
                enabled: false
                name:    bearer
            throw_exceptions:        false     # When an authentication failure occurs, return a 401 response immediately
            create_entry_point:      true      # When no authentication details are provided, create a default entry point that returns a 401 response
            authentication_provider: lexik_jwt_authentication.security.authentication.provider
            authentication_listener: lexik_jwt_authentication.security.authentication.listener
```
