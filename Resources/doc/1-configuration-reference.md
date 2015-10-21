Configuration reference
=======================

### Configuration reference

``` yaml
# app/config/config.yml
# ...
lexik_jwt_authentication:
    private_key_path:    %kernel.root_dir%/var/jwt/private.pem   # ssh private key path
    public_key_path:     %kernel.root_dir%/var/jwt/public.pem    # ssh public key path
    pass_phrase:         ''                                      # ssh key pass phrase
    token_ttl:           86400                                   # token ttl - defaults to 86400
    encoder_service:     lexik_jwt_authentication.jwt_encoder    # token encoder / decoder service - defaults to the jwt encoder (based on the namshi/jose library)
    user_identity_field: username                                # key under which the user identity will be stored in the token payload - defaults to username
```

### Security reference

#### Simplest configuration

``` yaml
# app/config/security.yml
# ...
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
