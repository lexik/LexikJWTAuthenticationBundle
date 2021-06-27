UPGRADE FROM 2.11 to 2.12
=========================

### Configuration

 * A new authenticator has been introduced for projects running on Symfony 5.3 
   with the new security-http' authenticator system.

   If you are using Symfony 5.3+ then consider updating your security configuration as follows.
   
   #### Before:
   
   ```yaml
   # config/packages/security.yaml
   
   security:
       firewalls:
           api:
               pattern: ^/api
               guard:
                   authenticators: 
                       - lexik_jwt_authentication.jwt_token_authenticator
   ```

   #### After:

   ```yaml
   # config/packages/security.yaml
   
   security:
       enable_authenticator_manager: true
       firewalls:
           api:
               pattern: ^/api
               jwt: ~
   ```

   _Note_  
   The changes above are related to this bundle only. 
   The new `jwt` authenticator behaves the same as the guard one, but you need to take care 
   of upgrading the rest of your security configuration according to the new authenticator system.  
   For more information, check https://symfony.com/doc/current/security/authenticator_manager.html.

### API

 * Added method `JWTTokenManagerInterface::parse()` which takes the raw token as only argument.
   Consider using this method over `decode()` as it is more inline with the new Symfony authenticator system.  
   The `JWTTokenManagerInterface::decode()` method may be deprecated in a future version.

 * Added class `Security\Authenticator\JWTAuthenticator` that is wired by the `JWTAuthenticatorFactory` class.
