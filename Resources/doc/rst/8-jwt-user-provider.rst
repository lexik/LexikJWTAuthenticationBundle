A database-less user provider
=============================

From `jwt.io <https://jwt.io/introduction>`__:

   Self-contained: The payload contains all the required information
   about the user, avoiding the need to query the database more than
   once. https://jwt.io/introduction

| A JWT is *self-contained*, meaning that we can trust into its payload
  for processing the authentication. In a nutshell, there should be no
  need for loading the user from the database when authenticating a JWT
  Token,
| the database should be hit only once for delivering the token.

That's why we decided to provide a user provider which is able to create
User instances from the JWT payload.

Configuring the user provider
-----------------------------

To work, the provider just needs a few lines of configuration:

.. code:: yaml

   # config/packages/security.yaml
   security:
       providers:
           jwt:
               lexik_jwt: ~

Then, use it on your JWT protected firewall:

.. code:: yaml

   # Symfony versions prior to 5.3
   security:
       firewalls:
           api:
               provider: jwt
               guard:
                   # ...

.. code:: yaml

   # Symfony 5.3 and higher
   security:
       firewalls:
           api:
               provider: jwt
               jwt: ~

What does it change?
--------------------

Now that the provider is configured, it will automatically be used by
the ``JWTTokenAuthenticator`` (Symfony < 5.3) or ``JWTAuthenticator``
(Symfony >= 5.3) when authenticating a token. Instead of loading the
user from a "datastore" (i.e. memory or any database engine), a
``JWTUserInterface`` instance will be created from the JWT payload, will
be cached for a request and be authenticated. We provide a simple
``JWTUser`` class implementing this interface, which is used by default
when configuring the provider.

Can I use my own user class?
----------------------------

Of course, you can. You just need to make your user class implement the
``JWTUserInterface`` interface. This interface contains only a
``createFromPayload()`` *named constructor* which takes the user's
username and the JWT token payload as arguments and returns an instance
of the class.

Sample implementation
'''''''''''''''''''''

.. code:: php

   namespace App\Security;

   final class User implements JWTUserInterface
   {
       // Your own logic
       
       public function __construct($username, array $roles, $email)
       {
           $this->username = $username;
           $this->roles = $roles;
           $this->email = $email;
       }
       
       public static function createFromPayload($username, array $payload)
       {
           return new self(
               $username,
               $payload['roles'], // Added by default
               $payload['email']  // Custom
           );
       }
   }

*Note*: You can extend the default ``JWTUser`` class if that fits your
needs.

Configuration
'''''''''''''

.. code:: yaml

   # config/packages/security.yaml
   providers:
       # ...
       jwt:
           lexik_jwt:
               class: App\Security\User

And voilà!
