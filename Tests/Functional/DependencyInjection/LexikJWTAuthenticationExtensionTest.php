<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\DependencyInjection;

use Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\TestCase;

/**
 * Tests the bundle extension and the configuration of services.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class LexikJWTAuthenticationExtensionTest extends TestCase
{
    /**
     * Tests that the encoder service and its configuration.
     */
    public function testEncoderConfiguration()
    {
        static::bootKernel();

        /* @var \Symfony\Component\DependencyInjection\ContainerInterface */
        $container           = static::$kernel->getContainer();
        $encoderNamespace    = 'lexik_jwt_authentication.encoder';
        $encryptionEngine    = $container->getParameter($encoderNamespace.'.encryption_engine');
        $encryptionAlgorithm = $container->getParameter($encoderNamespace.'.signature_algorithm');

        $jwsProviderMock = $this
            ->getMockBuilder('Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider')
            ->setConstructorArgs([
                $container->get('lexik_jwt_authentication.key_loader'),
                $encryptionEngine,
                $encryptionAlgorithm,
            ])
            ->getMock();

        $this->assertInstanceOf(
            'Lexik\Bundle\JWTAuthenticationBundle\Encoder\DefaultEncoder',
            $container->get($encoderNamespace)
        );

        // The configured engine is the one used by the service
        $this->assertAttributeEquals(
            'openssl' == $encryptionEngine ? 'OpenSSL' : 'SecLib',
            'encryptionEngine',
            $jwsProviderMock
        );

        // The configured algorithm is the one used by the service
        $this->assertAttributeEquals(
            $encryptionAlgorithm,
            'encryptionAlgorithm',
            $jwsProviderMock
        );
    }
}
