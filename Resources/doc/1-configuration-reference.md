Configuration reference
=======================

Bundle configuration
---------------------

### Minimal configuration

#### Using RSA/ECDSA

``` yaml
# config/packages/lexik_jwt_authentication.yaml
#...
lexik_jwt_authentication:
    secret_key: '%kernel.project_dir%/config/jwt/private.pem' # path to the secret key OR raw secret key, required for creating tokens
    public_key: '%kernel.project_dir%/config/jwt/public.pem'  # path to the public key OR raw public key, required for verifying tokens
    pass_phrase: 'yourpassphrase' # required for creating tokens
```

#### Using HMAC
``` yaml
#  config/packages/lexik_jwt_authentication.yaml
#...
lexik_jwt_authentication:
    secret_key: yoursecret
```

### Full default configuration

``` yaml
# config/packages/lexik_jwt_authentication.yaml
# ...
lexik_jwt_authentication:
    secret_key: ~
    public_key: ~
    pass_phrase: ~
    token_ttl: 3600 # token TTL in seconds, defaults to 1 hour
    user_identity_field: username  # key under which the user identity will be stored in the token payload
    clock_skew: 0

    # token encoding/decoding settings
    encoder:
        # token encoder/decoder service - default implementation based on the lcobucci/jwt library
        service:            lexik_jwt_authentication.encoder.lcobucci

        # encryption algorithm used by the encoder service
        signature_algorithm: RS256

    # token extraction settings
    token_extractors:
        # look for a token as Authorization Header
        authorization_header:
            enabled: true
            prefix:  Bearer
            name:    Authorization

        # check token in a cookie
        cookie:
            enabled: false
            name:    BEARER
            
        # check token in query string parameter
        query_parameter:
            enabled: false
            name:    bearer
        
        # check token in a cookie
        split_cookie:
            enabled: false
            cookies:
                - jwt_hp
                - jwt_s
```

#### Encoder configuration

##### service

Defaults to `lexik_jwt_authentication.encoder.lcobucci` which is based on the [Lcobucci/JWT](https://github.com/lcobucci/jwt) library.

For an advanced token encoding with higher encryption support, please see the [Spomky-Labs/lexik-jose-bridge](https://github.com/Spomky-Labs/lexik-jose-bridge) which is based on the great [web-token/jwt-framework](https://github.com/web-token/jwt-framework) library.

To create your own encoder service, see the [JWT encoder service customization chapter](5-encoder-service.md).

##### signature_algorithm

One of the algorithms supported by the default encoder for the configured [crypto engine](#crypto_engine).

- HS256, HS384, HS512 (HMAC)
- RS256, RS384, RS512 (RSA)
- ES256, ES384, ES512 (ECDSA)

#### Automatically generating cookies
You are now able to automatically generate secure and httpOnly cookies when the cookie token extractor is enabled [#753](https://github.com/lexik/LexikJWTAuthenticationBundle/pull/753).

```
token_extractors: 
    cookie: 
        enabled: true
        name: BEARER
# ...
set_cookies:
    BEARER: ~

# Full config with defaults:
#  BEARER:
#      lifetime: null (defaults to token ttl)
#      samesite: lax
#      path: /
#      domain: null (null means automatically set by symfony)
#      secure: true (default to true)
#      httpOnly: true

```

### Automatically generating split cookies
You are also able to automatically generate split cookies. Benefits of this approach are in [this post](https://medium.com/lightrail/getting-token-authentication-right-in-a-stateless-single-page-application-57d0c6474e3).

Keep in mind, that SameSite attribute is **not supported** in [some browsers](https://caniuse.com/#feat=same-site-cookie-attribute)

```
token_extractors:
    split_cookie:
        enabled: true
        cookies:
            - jwt_hp
            - jwt_s

set_cookies:
    jwt_hp:
        lifetime: null
        samesite: strict
        path: /
        domain: null
        httpOnly: false
        split:
            - header
            - payload

    jwt_s:
        lifetime: null
        samesite: strict
        path: /
        domain: null
        httpOnly: true
        split:
            - signature
```

Security configuration
-----------------------

```yaml
# config/packages/security.yaml
security:
    # ...
    providers:
        # ...
        jwt: # optional, any user provider can be used
            lexik_jwt:
                class: App\Security\JWTUser
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

