Extending JWTAuthenticator
===============================

The `JWTAuthenticator` class is responsible of authenticating JWT tokens. It is used through the `lexik_jwt_authentication.security.jwt_authenticator` abstract service which can be customized in the most flexible but still structured way to do it: _creating your own authenticators by extending the service_, so you can manage various security contexts in the same application.

Creating your own Authenticator
-------------------------------------

The following code can be used for creating your own authenticators.

Create the authenticator class extending the built-in one:

```php
namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator;

class CustomAuthenticator extends JWTAuthenticator
{
    // Your own logic
}
```

Same for the service definition:

```yaml
# config/services.yaml
services:
    app.custom_authenticator:
        class: App\Security\CustomAuthenticator
        parent: lexik_jwt_authentication.security.jwt_authenticator
```

Then, use it in your security configuration:

```yaml
# config/packages/security.yaml
security:
    # ...
    firewalls:
        # ...
        api:
            pattern:   ^/api
            stateless: true
            jwt: 
                authenticator: app.custom_authenticator

```

__Note:__ The code examples of this section require to have this step done, it may not be repeated.

Using different Token Extractors per Authenticator
--------------------------------------------------

Token extractors are set up in the main configuration of this bundle (see [configuration reference](1-configuration-reference.md#full-default-configuration)).
If your application contains multiple firewalls with different security contexts, you may want to configure the different token extractors which should be used on each firewall respectively. This can be done by having as much authenticators as firewalls (for creating authenticators, see [the first section of this topic](#creating-your-own-authenticator)).


```php
namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor;

class CustomAuthenticator extends JWTAuthenticator
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
