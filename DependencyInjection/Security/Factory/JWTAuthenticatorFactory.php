<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AuthenticatorFactoryInterface;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;

if (interface_exists(SecurityFactoryInterface::class) && !interface_exists(AuthenticatorFactoryInterface::class)) {
    eval('
        namespace Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\Security\Factory;

        use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AuthenticatorFactoryInterface;
        use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
        
        /**
         * Wires the "jwt" authenticator from user configuration.
         *
         * @author Robin Chalas <robin.chalas@gmail.com>
         */
        class JWTAuthenticatorFactory implements SecurityFactoryInterface
        {
            use JWTAuthenticatorFactoryTrait;
        }
    ');
} elseif (!method_exists(SecurityExtension::class, 'addAuthenticatorFactory')) {
    eval('
        namespace Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\Security\Factory;

        use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AuthenticatorFactoryInterface;
        use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
        
        /**
         * Wires the "jwt" authenticator from user configuration.
         *
         * @author Robin Chalas <robin.chalas@gmail.com>
         */
        class JWTAuthenticatorFactory implements AuthenticatorFactoryInterface, SecurityFactoryInterface
        {
            use JWTAuthenticatorFactoryTrait;
        }
    ');
} else {
    /**
     * Wires the "jwt" authenticator from user configuration.
     *
     * @author Robin Chalas <robin.chalas@gmail.com>
     */
    class JWTAuthenticatorFactory implements AuthenticatorFactoryInterface
    {
        use JWTAuthenticatorFactoryTrait;
    }
}
