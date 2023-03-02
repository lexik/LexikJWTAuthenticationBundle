UPGRADE FROM 3.x to 3.0
=======================

### Configuration

 * The JWT Authentication system based on Guard authenticator called `JWTTokenAuthenticator` has been removed.

 * The config options `private_key_path`, `public_key_path`, `encoder.crypto_engine` and `user_identity_field` have been removed.

### JOSE

 * The `DefaultJWTEncoder` class and the corresponding `lexik_jwt_authentication.encoder.default` service have been removed.

 * The `DefaultJWSProvider` class and the corresponding `lexik_jwt_authentication.jws_provider.default` service have been removed. 

### KeyLoader

 * BC: The `KeyLoaderInterface` interface have three new methods `getSigningKey`, `getPublicKey` and `getAdditionalPublicKeys`.

 * The `OpenSSLKeyLoader` class and the corresponding `lexik_jwt_authentication.key_loader.openssl` service have been removed.

### Security

 * The `JWTManagerInterface` interface has be removed.

 * The method `JWTTokenManagerInterface::setUserIdentityField` has been removed.

 * The method `PayloadAwareUserProviderInterface::loadUserByUsernameAndPayload` has been removed.

 * BC: The `PayloadAwareUserProviderInterface` interface has a new methods `loadUserByIdentifierAndPayload`.

 * BC: The `JWTTokenManagerInterface` interface have two new methods `createFromPayload` and `parse`.


