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
        tags:
            - { name: kernel.event_listener, event: lexik_jwt_authentication.on_jwt_created, method: onJWTCreated }
```

Example 1 : add client ip to the encoded payload

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
        if (!($request = $event->getRequest())) {
            return;
        }

        $payload       = $event->getData();
        $payload['ip'] = $request->getClientIp();

        $event->setData($payload);
    }
}
```

Example 2 : override token expiration date calcul to be more flexikble

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
        tags:
            - { name: kernel.event_listener, event: lexik_jwt_authentication.on_jwt_decoded, method: onJWTDecoded }
```

Example 3 : check client ip the decoded payload (from example 1)

``` php
// Acme\Bundle\ApiBundle\EventListener\JWTDecodedListener.php
class JWTDecodedListener
{
    /**
     * @param JWTDecodedEvent $event
     *
     * @return void
     */
    public function onJWTDecoded(JWTDecodedEvent $event)
    {
        if (!($request = $event->getRequest())) {
            return;
        }

        $payload = $event->getPayload();
        $request = $event->getRequest();

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