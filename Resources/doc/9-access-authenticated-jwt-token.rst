Accessing the authenticated JWT token
=====================================

If you need to get the information of JWT token from a Controller or
Service for some purposes, you can:

#. Inject *TokenStorageInterface* and *JWTTokenManagerInterface*:

   .. code-block:: php

       use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
       use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

       public function __construct(TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager)
       {
           $this->jwtManager = $jwtManager;
           $this->tokenStorageInterface = $tokenStorageInterface;
       }

#. Call ``decode()`` in jwtManager, and ``getToken()`` in TokenStorageInterface.

   .. code-block:: php

       $decodedJwtToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());

This returns the decoded information of the JWT token sent in the current request.
