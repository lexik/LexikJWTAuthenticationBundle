UPGRADE FROM 1.x to 2.0
=======================

Encoder
-------

* The service `lexik_jwt_authentication.jwt_encoder` has been removed in favor  
  of `lexik_jwt_authentication.encoder.default` that supports OpenSSL and  
  phpseclib encryption engines.
  
* The class `Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoder` has been  
  removed in favor of `Lexik\Bundle\JWTAuthenticationBundle\Encoder\DefaultEncoder`.

  It was used by the `lexik_jwt_authentication.jwt_encoder` service that has been removed.  
  
* The `Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface` has been changed,  
  the `encode` and `decode` methods now throw exceptions rather than returning `false`  
  in case of error.

KeyLoader
---------

* The `lexik_jwt_authentication.openssl_key_loader` has been removed  
  in favor of `lexik_jwt_authentication.key_loader`.

* The class `Lexik\Bundle\JWTAuthenticationBundle\Services\OpenSSLKeyLoader` has been  
  removed in favor of `Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\OpenSSLKeyLoader`.
  
  It was used by the `lexik_jwt_authentication.openssl_key_loader` that has been removed.
  
Command
-------

* The `lexik:jwt:check-open-ssl` command has been renamed to `lexik:jwt:check-config`  
  as the bundle now supports several encryption engines.
