UPGRADE FROM 1.x to 2.0
=======================

Events
-------

* The ability of retrieving `Request` instances from `Event` classes has been removed,
  as the current `Request` is no more injected into when they are dispatched.  
  Being able to access them was mainly useful for doing stuff depending on informations 
  retrieved from.  
  Fortunately, you can reproduce the same behaviour in a more efficient way:

  __Before__
  
  ```yaml
  services:
      jwt_event_listener:
          class: AppBundle\EventListener\JWTCreatedListener
          tags:
              - { name: kernel.event_listener, event: lexik_jwt_authentication.on_jwt_created, method: onJWTCreated }
  ```
  
  ```php
  use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
  
  class JWTCreatedListener
  {
      public function onJWTCreated(JWTCreatedEvent $event)
      {
          $request = $event->getRequest();
      }
  }
  ```
  
  __After__
  
  ```yaml
  services:
      jwt_event_listener:
          class: AppBundle\EventListener\JWTCreatedListener
          arguments: [ '@request_stack' ]
          tags:
              - { name: kernel.event_listener, event: lexik_jwt_authentication.on_jwt_created, method: onJWTCreated }
  ```
  
  ```php  
  use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
  use Symfony\Component\HttpFoundation\RequestStack;

  class JWTCreatedListener
  {
      private $requestStack;
      
      public function __construct(RequestStack $requestStack)
      {
          $this->requestStack = $requestStack;
      }
      
      public function onJWTCreated(JWTCreatedEvent $event)
      {
          $request = $this->requestStack->getCurrentRequest();
      }
  }
  ```

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
