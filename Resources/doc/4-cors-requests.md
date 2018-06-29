Working with CORS requests
==========================

There are several ways to add CORS requests handling capabilities to a Symfony application,
the fastest and most flexible solution being the [NelmioCorsBundle](https://github.com/nelmio/NelmioCorsBundle).

This bundle allows you to enable and configure CORS rules very precisely without having to modify your server configuration.
See the documentation for installation and usage instructions.

#### Example usage with the LexikJWTAuthenticationBundle

```yaml
nelmio_cors:
    ...
    paths:
        '^/api/':
            allow_origin: ['*']
            allow_headers: ['*']
            allow_methods: ['POST', 'PUT', 'GET', 'DELETE']
            max_age: 3600
```

The important thing to note here is that both the `login` and `api` firewalls paths
(in our example `/api/login_check` and `/api`) must be configured to allow CORS requests.
