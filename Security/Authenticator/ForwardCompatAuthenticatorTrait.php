<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;

$r = new \ReflectionMethod(AuthenticatorInterface::class, 'authenticate');

if ($r->hasReturnType() && Passport::class === $r->getReturnType()->getName()) {
    eval('
        namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator;
        
        use Symfony\Component\HttpFoundation\Request;
        use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

        /**
         * @internal
         */
        trait ForwardCompatAuthenticatorTrait
        {
            public function authenticate(Request $request): Passport
            {
                return $this->doAuthenticate($request);
            }
        }
    ');
} else {
    /**
     * @internal
     */
    trait ForwardCompatAuthenticatorTrait
    {
        public function authenticate(Request $request): PassportInterface
        {
            return $this->doAuthenticate($request);
        }
    }
}
