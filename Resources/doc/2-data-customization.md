Data customization and validation
=================================

**Careful**: Before you add your own custom data, know that the **JWT payload is not encrypted, it is only base64 encoded**. The token signature ensures its integrity (meaning it cannot be modified), but anyone can read its content (try it using a simple tool like [http://jwt.io/](http://jwt.io/)).

Table of contents
-----------------

* [Adding custom data or headers to the JWT payload](#eventsjwt_created---adding-custom-data-or-headers-to-the-jwt)
* [Validating data in the JWT payload](#eventsjwt_decoded---validating-data-in-the-jwt-payload)
* [Customize your security token](#eventsjwt_authenticated---customizing-your-security-token)
* [Adding public data to the JWT response](#eventsauthentication_success---adding-public-data-to-the-jwt-response)
* [Getting the JWT token string after encoding](#eventsjwt_encoded---getting-the-jwt-token-string-after-encoding)
* [Customizing the response on invalid credentials](#eventsauthentication_failure---customizing-the-failure-response-body)
* [Customizing the response on invalid token](#eventsjwt_invalid---customizing-the-invalid-token-response)
* [Customizing the response on token not found](#eventsjwt_not_found---customizing-the-response-on-token-not-found)
* [Customizing the response on expired token](#eventsjwt_expired---customizing-the-response-message-on-expired-token)

Adding custom data or headers to the JWT
--------------------------------------------------------------

#### Using Events::JWT_CREATED

By default the JWT payload will contain the username and the token TTL,
but you can add your own data.

You can also modify the header to fit on your application context.

``` yaml
# config/services.yaml
services:
    acme_api.event.jwt_created_listener:
        class: App\EventListener\JWTCreatedListener
        arguments: [ '@request_stack' ]
        tags:
            - { name: kernel.event_listener, event: lexik_jwt_authentication.on_jwt_created, method: onJWTCreated }
```

##### Example: Add client ip to the encoded payload

``` php
// src/App/EventListener/JWTCreatedListener.php

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @var RequestStack
 */
private $requestStack;

/**
 * @param RequestStack $requestStack
 */
public function __construct(RequestStack $requestStack)
{
    $this->requestStack = $requestStack;
}

/**
 * @param JWTCreatedEvent $event
 *
 * @return void
 */
public function onJWTCreated(JWTCreatedEvent $event)
{
    $request = $this->requestStack->getCurrentRequest();

    $payload       = $event->getData();
    $payload['ip'] = $request->getClientIp();

    $event->setData($payload);
    
    $header        = $event->getHeader();
    $header['cty'] = 'JWT';

    $event->setHeader($header);
}
```

##### Example: Override token expiration date calculation to be more flexible

``` php
// src/App/EventListener/JWTCreatedListener.php

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

/**
 * @param JWTCreatedEvent $event
 *
 * @return void
 */
public function onJWTCreated(JWTCreatedEvent $event)
{
    $expiration = new \DateTime('+1 day');
    $expiration->setTime(2, 0, 0);

    $payload        = $event->getData();
    $payload['exp'] = $expiration->getTimestamp();

    $event->setData($payload);
}
```

#### Using a custom payload at JWT creation

If you [create JWT tokens programmatically](./7-manual-token-creation.md), you can add custom data to the JWT using the method `createFromPayload(UserInterface $user, array $payload)`

```php
$payload = ['foo' => 'bar'];

$jwt = $this->container->get('lexik_jwt_authentication.jwt_manager')->createFromPayload($user);
``` 

Events::JWT_DECODED - Validating data in the JWT payload
--------------------------------------------------------

You can access the jwt payload once it has been decoded to perform your own additional validation.

``` yaml
# config/services.yaml
services:
    acme_api.event.jwt_decoded_listener:
        class: App\EventListener\JWTDecodedListener
        arguments: [ '@request_stack' ]
        tags:
            - { name: kernel.event_listener, event: lexik_jwt_authentication.on_jwt_decoded, method: onJWTDecoded }
```

#### Example: Check client ip the decoded payload (from example 1)

``` php
// src/App/EventListener/JWTDecodedListener.php

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;

/**
 * @param JWTDecodedEvent $event
 *
 * @return void
 */
public function onJWTDecoded(JWTDecodedEvent $event)
{
    $request = $this->requestStack->getCurrentRequest();
    
    $payload = $event->getPayload();

    if (!isset($payload['ip']) || $payload['ip'] !== $request->getClientIp()) {
        $event->markAsInvalid();
    }
}
```

#### Example: Add additional data to payload - to get it in your [custom UserProvider](8-jwt-user-provider.md)

``` php
// src/App/EventListener/JWTDecodedListener.php

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;

/**
 * @param JWTDecodedEvent $event
 *
 * @return void
 */
public function onJWTDecoded(JWTDecodedEvent $event)
{
    $payload = $event->getPayload();
    $user = $this->userRepository->findOneByUsername($payload['username']);

    $payload['custom_user_data'] = $user->getCustomUserInformations();

    $event->setPayload($payload); // Don't forget to regive the payload for next event / step
}
```

Events::JWT_AUTHENTICATED - Customizing your security token
------------------------------------------------------------

You can add attributes to the token once it has been authenticated to allow JWT properties to be used by your application.

``` yaml
# config/services.yaml
services:
    acme_api.event.jwt_authenticated_listener:
        class: App\EventListener\JWTAuthenticatedListener
        tags:
            - { name: kernel.event_listener, event: lexik_jwt_authentication.on_jwt_authenticated, method: onJWTAuthenticated }
```

#### Example: Keep a UUID that was set into the JWT in the authenticated token

``` php
// src/App/EventListener/JWTAuthenticatedListener.php

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;

/**
 * @param JWTAuthenticatedEvent $event
 *
 * @return void
 */
public function onJWTAuthenticated(JWTAuthenticatedEvent $event)
{
    $token = $event->getToken();
    $payload = $event->getPayload();

    $token->setAttribute('uuid', $payload['uuid']);
}
```

Events::AUTHENTICATION_SUCCESS - Adding public data to the JWT response
-----------------------------------------------------------------------

By default, the authentication response is just a json containing the JWT but you can add your own public data to it.

``` yaml
# config/services.yaml
services:
    acme_api.event.authentication_success_listener:
        class: App\EventListener\AuthenticationSuccessListener
        tags:
            - { name: kernel.event_listener, event: lexik_jwt_authentication.on_authentication_success, method: onAuthenticationSuccessResponse }
```

#### Example: Add user roles to the response body

``` php
// src/App/EventListener/AuthenticationSuccessListener.php

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

/**
 * @param AuthenticationSuccessEvent $event
 */
public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
{
    $data = $event->getData();
    $user = $event->getUser();

    if (!$user instanceof UserInterface) {
        return;
    }

    $data['data'] = array(
        'roles' => $user->getRoles(),
    );

    $event->setData($data);
}
```

Events::JWT_ENCODED - Getting the JWT token string after encoding
-----------------------------------------------------------------

You may need to get JWT after its creation.

#### Example: Obtain JWT string

``` php
// src/App/EventListener/JWTEncodedListener.php

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTEncodedEvent;

/**
 * @param JWTEncodedEvent $event
 */
public function onJwtEncoded(JWTEncodedEvent $event)
{
    $token = $event->getJWTString();
}
```

Events::AUTHENTICATION_FAILURE - Customizing the failure response body
----------------------------------------------------------------------

By default, the response in case of failed authentication is just a json containing a failure message and a 401 status code, but you can set a custom response.

``` yaml
# config/services.yaml
services:
    acme_api.event.authentication_failure_listener:
        class: App\EventListener\AuthenticationFailureListener
        tags:
            - { name: kernel.event_listener, event: lexik_jwt_authentication.on_authentication_failure, method: onAuthenticationFailureResponse }
```

Example: Set a custom response on authentication failure

``` php
// src/App/EventListener/AuthenticationFailureListener.php

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;

/**
 * @param AuthenticationFailureEvent $event
 */
public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event)
{
    $data = [
        'status'  => '401 Unauthorized',
        'message' => 'Bad credentials, please verify that your username/password are correctly set',
    ];

    $response = new JWTAuthenticationFailureResponse($data);

    $event->setResponse($response);
}
```

Events::JWT_INVALID - Customizing the invalid token response
------------------------------------------------------------

By default, if the token is invalid, the response is just a json containing the corresponding error message and a 401 status code, but you can set a custom response.

``` yaml
# config/services.yaml
services:
    acme_api.event.jwt_invalid_listener:
        class: App\EventListener\JWTInvalidListener
        tags:
            - { name: kernel.event_listener, event: lexik_jwt_authentication.on_jwt_invalid, method: onJWTInvalid }
```

#### Example: Set a custom response message and status code on invalid token

``` php
// src/App/EventListener/JWTInvalidListener.php

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;

/**
 * @param JWTInvalidEvent $event
 */
public function onJWTInvalid(JWTInvalidEvent $event)
{
    $response = new JWTAuthenticationFailureResponse('Your token is invalid, please login again to get a new one', 403);

    $event->setResponse($response);
}
```

Events::JWT_NOT_FOUND - Customizing the response on token not found
------------------------------------------------------------------

By default, if no token is found in a request, the authentication listener will either call the entry point that returns a unauthorized (401) json response, or (if the firewall allows anonymous requests), just let the request continue.  
Thanks to this event, you can set a custom response.

``` yaml
# config/services.yaml
services:
    acme_api.event.jwt_notfound_listener:
        class: App\EventListener\JWTNotFoundListener
        tags:
            - { name: kernel.event_listener, event: lexik_jwt_authentication.on_jwt_not_found, method: onJWTNotFound }
```

#### Example: Set a custom response message on token not found

``` php
// src/App/EventListener/JWTNotFoundListener.php

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @param JWTNotFoundEvent $event
 */
public function onJWTNotFound(JWTNotFoundEvent $event)
{
    $data = [
        'status'  => '403 Forbidden',
        'message' => 'Missing token',
    ];

    $response = new JsonResponse($data, 403);

    $event->setResponse($response);
}
```

Events::JWT_EXPIRED - Customizing the response message on expired token
------------------------------------------------------------------------

By default, if the token provided in the request is expired, the authentication listener will call the entry point returning an unauthorized (401) json response.
Thanks to this event, you can set a custom response or simply change the response message.

``` yaml
# config/services.yaml
services:
    acme_api.event.jwt_expired_listener:
        class: App\EventListener\JWTExpiredListener
        tags:
            - { name: kernel.event_listener, event: lexik_jwt_authentication.on_jwt_expired, method: onJWTExpired }
```

#### Example: Customize the response in case of expired token

``` php
// src/App/EventListener/JWTExpiredListener.php

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;

/**
 * @param JWTExpiredEvent $event
 */
public function onJWTExpired(JWTExpiredEvent $event)
{
    /** @var JWTAuthenticationFailureResponse */
    $response = $event->getResponse();

    $response->setMessage('Your token is expired, please renew it.');
}
```

__Protip:__ You might want to use the same method for customizing the response on both `JWT_INVALID`, `JWT_NOT_FOUND` and/or `JWT_EXPIRED` events. 
For that, use the `Lexik\Bundle\JWTAuthenticationBundle\Event\JWTFailureEventInterface` interface to type-hint the event argument of your listener's method instead of the concrete class corresponding to one of these specific events.
