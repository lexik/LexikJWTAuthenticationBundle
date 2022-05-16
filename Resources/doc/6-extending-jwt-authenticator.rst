Extending Authenticator
=======================

The ``JWTTokenAuthenticator`` (Symfony < 5.3) or ``JWTAuthenticator`` (Symfony >= 5.3)
class is responsible of authenticating JWT tokens. It is used through the
``lexik_jwt_authentication.security.guard.jwt_token_authenticator`` (Symfony < 5.3)
or ``lexik_jwt_authentication.security.jwt_authenticator`` (Symfony >= 5.3)
abstract service which can be customized in the most flexible but still
structured way to do it: *creating your own authenticators by extending
the service*, so you can manage various security contexts in the same
application.

.. _creating-your-own-authenticator:

Creating your own Authenticator
-------------------------------

For Symfony versions prior to 5.3
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: php

    namespace App\Security\Guard;

    use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator as BaseAuthenticator;

    class JWTTokenAuthenticator extends BaseAuthenticator
    {
        // Your own logic
    }

.. code-block:: yaml

    # config/services.yaml
    services:
        app.jwt_token_authenticator:
            class: App\Security\Guard\JWTTokenAuthenticator
            parent: lexik_jwt_authentication.security.guard.jwt_token_authenticator

.. code-block:: yaml

    # config/packages/security.yaml
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

For Symfony 5.3 and higher
~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: php

    namespace App\Security;

    use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator;

    class CustomAuthenticator extends JWTAuthenticator
    {
        // Your own logic
    }

.. code-block:: yaml

    # config/services.yaml
    services:
        app.custom_authenticator:
            class: App\Security\CustomAuthenticator
            parent: lexik_jwt_authentication.security.jwt_authenticator

.. code-block:: yaml

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

.. note::

    The code examples of this section require to have this step
    done, it may not be repeated.

Using different Token Extractors per Authenticator
--------------------------------------------------

Token extractors are set up in the main configuration of this bundle
(see :doc:`configuration reference </1-configuration-reference>`).
If your application contains multiple firewalls with different security
contexts, you may want to configure the different token extractors which
should be used on each firewall respectively. This can be done by having
as much authenticators as firewalls (for creating authenticators, see
:ref:`the first section of this topic <creating-your-own-authenticator>`).

You can overwrite the ``getTokenExtractor()`` in custom authenticator:

.. code-block:: php

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
