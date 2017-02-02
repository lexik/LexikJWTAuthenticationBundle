Configuration reference
=======================

Bundle configuration
---------------------

### Minimal configuration

``` yaml
# app/config/config.yml
#...
lexik_jwt_authentication:
    private_key_path:    '%kernel.root_dir%/../var/jwt/private.pem'
    # ssh public key path
    public_key_path:     '%kernel.root_dir%/../var/jwt/public.pem'
    # ssh key pass phrase
    pass_phrase:         ''
```

### Full default configuration

``` yaml
# app/config/config.yml
# ...
lexik_jwt_authentication:
    # ssh private key path
    private_key_path:    '%kernel.root_dir%/../var/jwt/private.pem'
    # ssh public key path
    public_key_path:     '%kernel.root_dir%/../var/jwt/public.pem'
    # ssh key pass phrase
    pass_phrase:         ''
    # token ttl
    token_ttl:           3600
    # key under which the user identity will be stored in the token payload
    user_identity_field: username

    # token encoding/decoding settings
    encoder:
        # token encoder/decoder service - default implementation based on the namshi/jose library
        service:            lexik_jwt_authentication.encoder.default
        # crypto engine used by the encoder service
        crypto_engine:  openssl
        # encryption algorithm used by the encoder service
        signature_algorithm: RS256

    # token extraction settings
    token_extractors:
        authorization_header:      # look for a token as Authorization Header
            enabled: true
            prefix:  Bearer
            name:    Authorization
        cookie:                    # check token in a cookie
            enabled: false
            name:    BEARER
        query_parameter:           # check token in query string parameter
            enabled: false
            name:    bearer
```

#### Encoder configuration

##### service

Default to `lexik_jwt_authentication.encoder.default` which is based on the [Namshi/JOSE](https://github.com/namshi/jose) library.  
You can also use `lexik_jwt_authentication.encoder.lcobucci` which is based on the [Lcobucci/JWT](https://github.com/lcobucci/jwt) library and concern the same usage level as the default one, providing an easy way to validate claims.

For an advanced token encoding with higher encryption support, please see the [`Spomky-Labs/lexik-jose-bridge`](https://github.com/Spomky-Labs/lexik-jose-bridge) which is based on the great [`Spomky-Labs/JOSE`](https://github.com/Spomky-Labs/JOSE) library.

To create your own encoder service, see the [JWT encoder service customization chapter](5-encoder-service.md).

##### crypto_engine

One of `openssl` and `phpseclib`, the crypto engines supported by the default token encoder service.  
See the [OpenSSL](https://github.com/openssl/openssl) and [phpseclib](https://github.com/phpseclib/phpseclib) documentations for more information.

##### signature_algorithm

One of the algorithms supported by the default encoder for the configured [crypto engine](#crypto_engine).

__Supported algorithms for OpenSSL:__
- RS256, RS384, RS512 (RSA)
- ES256, ES384, ES512 (ECDSA)
- HS256, HS384, HS512 (HMAC)

__Supported algorithms for phpseclib:__
- RS256, RS384, RS512 (RSA)

Security configuration
-----------------------

```yaml
# app/config/security.yml
security:
    # ...
    providers:
        # ...
        jwt: # optional
            lexik_jwt:
                class: AppBundle\Security\JWTUser
    firewalls:
        # ...
        api:
            # ...
            guard:
                authenticators: 
                    - lexik_jwt_authentication.jwt_token_authenticator
            provider: jwt # optional
```

##### authenticator

For more details about the `lexik_jwt_authentication.jwt_token_authenticator` service and how to customize it, see ["Extending the Guard JWTTokenAuthenticator"](6-extending-jwt-authenticator.md)

##### database-less user provider

For a database-less authentication (i.e. trusting into the JWT data instead of reloading the user from the database), see ["A database less user provider"](8-jwt-user-provider.md).

