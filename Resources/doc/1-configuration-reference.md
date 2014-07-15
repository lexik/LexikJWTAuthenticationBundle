Configuration reference
=======================

#### Simplest configuration

``` yaml
# ...
firewalls:
    # ...
    api:
        # ...
        lexik_jwt: ~ # check token in Authorization Header, with a value prefix of 'Bearer'
```

#### Full configuration

``` yaml
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
            throw_exceptions: false     # When an authentication failure occurs, return a 401 response immediately
            create_entry_point: true    # When no authentication details are provided, create a default entry point that returns a 401 response
```
