UPGRADE FROM 2.x to 2.5
=======================

Configuration
-------------

* The following config options have been deprecated and will be removed in 3.0:

  - `private_key_path`: Replaced by `secret_key` which accepts a raw key (string) as value or a file path.

  - `public_key_path`: Replaced by `public_key` which accepts a raw key (string) as value or a file path.

  - `encoder.crypto_engine`: Support for using PHPSecLib will be removed in 3.0 along with the dependency to
    the `namshi/jose` library (see below). As such, OpenSSL will remain the only supported crypto engine.

* Only one of `public_key` and `secret_key` is required, which makes it possible for a server to be the
  unique secret key holder thus the only part being able to deliver tokens.
  Clients can just hold the public key only for token verification.
  
JOSE
----

* The `DefaultJWTEncoder` class and the corresponding `lexik_jwt_authentication.encoder.default` service
  have been deprecated. It is based on the [namshi/jose](https://github.com/namshi/jose) library which itself
  is deprecated. The bundle now uses the [lcobucci/jwt](https://github.com/lcobucci/jwt) library as default JOSE library.
  Set the `encoder.service` configuration key to `lexik_jwt_authentication.encoder.lcobucci` or omit it instead 
  (relying on the default value).
  
  **We highly recommend to upgrade as early as possible since the namshi/jose library might contain known security issues as time goes by**.
