Accessing the authenticated JWT token
=====================================

If you need to get the information of JWT token from a Controller or Service for some purposes, you can:

1. Inject _TokenStorageInterface_ and _JWTTokenManagerInterface_:

```php
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

public function __construct(TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager)
{
    $this->jwtManager = $jwtManager;
    $this->tokenStorageInterface = $tokenStorageInterface;
}
```

2. Call `decode()` in jwtManager, and `getToken()` in tokenStorageInterface.

```php
$decodedJwtToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
```

This returns the decoded information of the JWT token sent in the current request.