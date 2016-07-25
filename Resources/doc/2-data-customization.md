Data customization and validation
=================================

**Careful**: Before you add your own custom data, know that the **JWT payload is not encrypted, it is only base64 encoded**. The token signature ensures its integrity (meaning it cannot be modified), but anyone can read its content (try it using a simple tool like [http://jwt.io/](http://jwt.io/)).

#### Events::JWT_CREATED - add data to the JWT payload

By default the JWT payload will contain the username and the token TTL,
but you can add your own data.

``` yaml
# services.yml
services:
    acme_api.event.jwt_created_listener:
        class: Acme\Bundle\ApiBundle\EventListener\JWTCreatedListener
        arguments: [ '@request_stack' ] # Symfony 2.4+
        tags:
            - { name: kernel.event_listener, event: lexik_jwt_authentication.on_jwt_created, method: onJWTCreated }
```

Example 1 : add client ip to the encoded payload

``` php
// Acme\Bundle\ApiBundle\EventListener\JWTCreatedListener.php

use Symfony\Component\HttpFoundation\RequestStack;

class JWTCreatedListener
{
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
  
    // ...
    
    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        // Symfony < 2.4
        $request = $event->getRequest();
        
        // Symfony 2.4+
        $request = $this->requestStack->getCurrentRequest();

        $payload       = $event->getData();
        $payload['ip'] = $request->getClientIp();

        $event->setData($payload);
    }
}
```

Example 2 : override token expiration date calcul to be more flexible

``` php
// Acme\Bundle\ApiBundle\EventListener\JWTCreatedListener.php
class JWTCreatedListener
{  
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
}
```

#### Events::JWT_DECODED - validate data in the JWT payload

You can access the jwt payload once it has been decoded to perform you own additional validation.

``` yaml
# services.yml
services:
    acme_api.event.jwt_decoded_listener:
        class: Acme\Bundle\ApiBundle\EventListener\JWTDecodedListener
        arguments: [ '@request_stack' ] # Symfony 2.4+
        tags:
            - { name: kernel.event_listener, event: lexik_jwt_authentication.on_jwt_decoded, method: onJWTDecoded }
```

Example 3 : check client ip the decoded payload (from example 1)

``` php
// Acme\Bundle\ApiBundle\EventListener\JWTDecodedListener.php
class JWTDecodedListener
{
    // ...
  
    /**
     * @param JWTDecodedEvent $event
     *
     * @return void
     */
    public function onJWTDecoded(JWTDecodedEvent $event)
    {
        // Symfony < 2.4
        $request = $event->getRequest();
        
        // Symfony 2.4+
        $request = $this->requestStack->getCurrentRequest();
        
        $payload = $event->getPayload();

        if (!isset($payload['ip']) || $payload['ip'] !== $request->getClientIp()) {
            $event->markAsInvalid();
        }
    }
}
```

#### Events::JWT_AUTHENTICATED - customize your authenticated token

You can add attributes to the token once it has been authenticated to allow JWT properties to be used by your application.

``` yaml
# services.yml
services:
    acme_api.event.jwt_authenticated_listener:
        class: Acme\Bundle\ApiBundle\EventListener\JWTAuthenticatedListener
        tags:
            - { name: kernel.event_listener, event: lexik_jwt_authentication.on_jwt_authenticated, method: onJWTAuthenticated }
```

Example 4 : Keep a UUID that was set into the JWT in the authenticated token

``` php
// Acme\Bundle\ApiBundle\EventListener\JWTAuthenticatedListener.php
class JWTAuthenticatedListener
{
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
}
```

#### Events::AUTHENTICATION_SUCCESS - add public data to the JWT response

By default, the authentication response is just a json containing the JWT but you can add your own public data to it.

``` yaml
# services.yml
services:
    acme_api.event.authentication_success_listener:
        class: Acme\Bundle\ApiBundle\EventListener\AuthenticationSuccessListener
        tags:
            - { name: kernel.event_listener, event: lexik_jwt_authentication.on_authentication_success, method: onAuthenticationSuccessResponse }
```

Example 5 : add user roles to the response

``` php
// Acme\Bundle\ApiBundle\EventListener\AuthenticationSuccessListener.php
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

    // $data['token'] contains the JWT

    $data['data'] = array(
        'roles' => $user->getRoles(),
    );

    $event->setData($data);
}
```

#### Events::JWT_ENCODED - get JWT string

You may need to get JWT after its creation.

Example 6: obtain JWT string

``` php
// Acme\Bundle\ApiBundle\EventListener\OnJwtEncoded.php

/**
 * @param JWTEncodedEvent $event
 */
public function onJwtEncoded(JWTEncodedEvent $event)
{
    $token = $event->getJWTString();
}
```

#### Events::AUTHENTICATION_FAILURE - customize the failure response

By default, the response in case of failed authentication is just a json containing a "Bad credentials" message and a 401 status code, but you can set a custom response.

``` yaml
# services.yml
services:
    acme_api.event.authentication_failure_listener:
        class: Acme\Bundle\ApiBundle\EventListener\AuthenticationFailureListener
        tags:
            - { name: kernel.event_listener, event: lexik_jwt_authentication.on_authentication_failure, method: onAuthenticationFailureResponse }
```

Example 7: set a custom response on authentication failure

``` php
// Acme\Bundle\ApiBundle\EventListener\AuthenticationFailureListener.php
/**
 * @param AuthenticationFailureEvent $event
 */
public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event)
{
    $data = [
        'status'  => '401 Unauthorized',
        'message' => 'Bad credentials, please verify that your username/password are correctly set',
    ];

    $response = new JsonResponse($data, 401);

    $event->setResponse($response);
}
```

#### Events::JWT_INVALID - customize the invalid token response

By default, if the token is invalid, the response is just a json containing the corresponding error message and a 401 status code, but you can set a custom response.

``` yaml
# services.yml
services:
    acme_api.event.jwt_invalid_listener:
        class: Acme\Bundle\ApiBundle\EventListener\JWTInvalidListener
        tags:
            - { name: kernel.event_listener, event: lexik_jwt_authentication.on_jwt_invalid, method: onJWTInvalid }
```

Example 8: set a custom response message on invalid token

``` php
// Acme\Bundle\ApiBundle\EventListener\JWTInvalidListener.php
/**
 * @param JWTInvalidEvent $event
 */
public function onJWTInvalid(JWTInvalidEvent $event)
{
    $data = [
        'status'  => '403 Forbidden',
        'message' => 'Your token is invalid, please login again to get a new one',
    ];

    $response = new JsonResponse($data, 403);

    $event->setResponse($response);
}
```

__Note:__ This feature is not available if the `throw_exceptions` firewall option is set to `true`.

#### Events::JWT_NOT_FOUND - customize the response on token not found

By default, if no token is found in a request, the authentication listener will either call the entry point that returns a unauthorized (401) json response, or (if the firewall allows anonymous requests), just let the request continue.  
Thanks to this event, you can set a custom response.

``` yaml
# services.yml
services:
    acme_api.event.jwt_invalid_listener:
        class: Acme\Bundle\ApiBundle\EventListener\JWTInvalidListener
        tags:
            - { name: kernel.event_listener, event: lexik_jwt_authentication.on_jwt_not_found, method: onJWTNotFound }
```

Example 8: set a custom response message on token not found

``` php
// Acme\Bundle\ApiBundle\EventListener\JWTNotFoundListener.php

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;

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

__Protip:__ You might want to use the same method for customizing the response on both `JWT_INVALID` and `JWT_NOT_FOUND` events. 
For that, use the `Event\JWTFailureEventInterface` interface to typehint the event argument of your listener's method, rather than 
a specific event class (i.e. `JWTNotFoundEvent` or `JWTInvalidEvent`).
