Extending JWTTokenAuthenticator
===============================

The `JWTTokenAuthenticator` class is responsible of authenticating JWT tokens. It is used through the `lexik_jwt_authentication.security.guard.jwt_token_authenticator` abstract service which can be customized in the most flexible but still structured way to do it: _creating your own authenticators by extending the service_, so you can manage various security contexts in the same application.

Creating your own Token Authenticator
-------------------------------------

The following code can be used for creating your own authenticators.

Create the authenticator class extending the built-in one:

```php
namespace AppBundle\Security\Guard;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator as BaseAuthenticator;

class JWTTokenAuthenticator extends BaseAuthenticator
{
    // Your own logic
}
```

Same for the service definition:

```yaml
# services.yml
services:
    app.jwt_token_authenticator:
        class: AppBundle\Security\Guard\JWTTokenAuthenticator
        parent: lexik_jwt_authentication.security.guard.jwt_token_authenticator
```

Then, use it in your security configuration:

```yaml
# app/config/security.yml
security:
    # ...
    firewalls:
        # ...
        api:
            pattern:   ^/api
            stateless: true
            guard: 
                authenticators:
                    - app.jwt_token_authenticator

```

__Note:__ The code examples of this section require to have this step done, it may not be repeated.

Using different Token Extractors per Authenticator
--------------------------------------------------

Token extractors are set up in the main configuration of this bundle, usually found in your `app/config/config.yml`.  
If your application contains multiple firewalls with different security contexts, you may want to configure the different token extractors which should be used on each firewall respectively. This can be done by having as much authenticators as firewalls (for creating authenticators, see [the first section of this topic](#creating-your-own-token-authenticator)).


```php
namespace AppBundle\Security\Guard;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator as BaseAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor;

class JWTTokenAuthenticator extends BaseAuthenticator
{
    /**
     * @return TokenExtractor\TokenExtractorInterface
     */
    protected function getTokenExtractor()
    {
        // Return a custom extractor, no matter of what are configured
        return new TokenExtractor\AuthorizationHeaderTokenExtractor('Token', 'Authorization');

        // Or retrieve the chain token extractor for mapping/unmapping extractors for this authenticator
        $chainExtractor = parent::getTokenExtractor();
        
        // Clear the token extractor map from all configured extractors
        $chainExtractor->clearMap();
        
        // Or only remove a specific extractor
        $chainTokenExtractor->removeExtractor(function (TokenExtractor\TokenExtractorInterface $extractor) {
            return $extractor instanceof TokenExtractor\CookieTokenExtractor;
        });
        
        // Add a new query parameter extractor to the configured ones
        $chainExtractor->addExtractor(new TokenExtractor\QueryParameterTokenExtractor('jwt'));
        
        // Return the chain token extractor with the new map
        return $chainTokenExtractor;
    }
}
```
