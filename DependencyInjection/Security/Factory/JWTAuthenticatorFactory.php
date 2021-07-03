<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AuthenticatorFactoryInterface;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Wires the "jwt" authenticator from user configuration.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class JWTAuthenticatorFactory implements SecurityFactoryInterface, AuthenticatorFactoryInterface
{
    /**
     * @throws \LogicException
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        throw new \LogicException('This method is implemented for BC purpose and should never be called.');
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'jwt';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(NodeDefinition $node)
    {
        // no-op - no config here for now
    }

    public function createAuthenticator(ContainerBuilder $container, string $firewallName, array $config, string $userProviderId)
    {
        $authenticatorId = 'security.authenticator.jwt.'.$firewallName;
        $container
            ->setDefinition($authenticatorId, new ChildDefinition('lexik_jwt_authentication.security.jwt_authenticator'))
            ->replaceArgument(3, new Reference($userProviderId));

        $this->removeLegacyGuardServices($container);

        return $authenticatorId;
    }

    private function removeLegacyGuardServices(ContainerBuilder $container)
    {
        $container->removeAlias('lexik_jwt_authentication.jwt_token_authenticator');
        $container->removeDefinition('lexik_jwt_authentication.security.guard.jwt_token_authenticator');
    }
}
