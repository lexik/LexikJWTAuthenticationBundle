Configuration reference
=======================

### Configuration reference
``` yaml
# app/config/config.yml

services:
    lexik_jwt_shared_key_encoder:
        class: Lexik\Bundle\JWTAuthenticationBundle\Encoder\SharedKeyJWTEncoder
        arguments: [ %shared_key_algorithm%, %shared_key% ]

    lexik_jwt_public_private_key_encoder:
        class: Lexik\Bundle\JWTAuthenticationBundle\Encoder\PublicPrivateKeyJWTEncoder
        arguments: [ %public_private_key_algorithm%, %private_key_path%, %public_key_path%, %pass_phrase% ]

lexik_jwt_authentication:
    token_ttl:           86400                        # token ttl - defaults to 86400
    encoder_service:     lexik_jwt_shared_key_encoder # token encoder/decoder service
    user_identity_field: username                     # key under which the user identity will be stored in the token payload - defaults to username
```

### Parameters reference
``` yaml
# app/config/parameters.yml

parameters:
    # Parameters for PublicPrivateKeyJWTEncoder
    public_private_key_algorithm: RS256
    public_key_path:              /path/to/keys/public.pem
    private_key_path:             /path/to/keys/private.pem
    pass_phrase:                  YourPassPhrase

    # Parameters for SharedKeyJWTEncoder
    shared_key_algorithm: HS512
    shared_key:           YourSharedKey
```


### Security reference

#### Simplest configuration
You can start using the JWTAuthentication by enabling the default configuration.

``` yaml
# app/config/security.yml

firewalls:
    # ...
    api:
        # ...
        lexik_jwt: ~ # check token in Authorization Header, with a value prefix of 'Bearer'
```

#### Full configuration

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
            query_parameter:      # check token in query string parameter
                enabled: true
                name:    bearer
            throw_exceptions:        false     # When an authentication failure occurs, return a 401 response immediately
            create_entry_point:      true      # When no authentication details are provided, create a default entry point that returns a 401 response
            authentication_provider: lexik_jwt_authentication.security.authentication.provider
```
