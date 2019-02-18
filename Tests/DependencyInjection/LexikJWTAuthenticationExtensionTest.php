<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\DependencyInjection;

use Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\LexikJWTAuthenticationExtension;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\DefaultEncoder;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\LcobucciJWTEncoder;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\Compiler\ResolveChildDefinitionsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Tests the bundle extension and the configuration of services.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class LexikJWTAuthenticationExtensionTest extends TestCase
{
    public function testEncoderConfiguration()
    {
        $container  = $this->getContainer(['secret_key' => 'private.pem', 'public_key' => 'public.pem', 'pass_phrase' => 'test']);
        $encoderDef = $container->findDefinition('lexik_jwt_authentication.encoder');
        $this->assertSame(LcobucciJWTEncoder::class, $encoderDef->getClass());
        $this->assertEquals(new Reference('lexik_jwt_authentication.jws_provider.lcobucci'), $encoderDef->getArgument(0));
        $this->assertEquals(
            [
                new Reference('lexik_jwt_authentication.key_loader.raw'),
                '%lexik_jwt_authentication.encoder.crypto_engine%',
                '%lexik_jwt_authentication.encoder.signature_algorithm%',
                '%lexik_jwt_authentication.token_ttl%',
                '%lexik_jwt_authentication.clock_skew%',
            ],
            $container->getDefinition('lexik_jwt_authentication.jws_provider.lcobucci')->getArguments()
        );
        $this->assertSame(
            ['private.pem', 'public.pem', '%lexik_jwt_authentication.pass_phrase%'],
            $container->getDefinition('lexik_jwt_authentication.key_loader.raw')->getArguments()
        );
    }

    /**
     * @group legacy
     * @expectedDeprecation Using "lexik_jwt_authentication.encoder.default" as encoder service is deprecated since LexikJWTAuthenticationBundle 2.5, use "lexik_jwt_authentication.encoder.lcobucci" (default) or your own encoder service instead.
     */
    public function testDeprecatedDefaultEncoderService()
    {
        $container = $this->getContainer([
            'secret_key'  => 'private.pem',
            'public_key'  => 'public.pem',
            'pass_phrase' => 'test',
            'encoder'     => ['service' => 'lexik_jwt_authentication.encoder.default'],
        ]);
        $encoderDef = $container->findDefinition('lexik_jwt_authentication.encoder');
        $this->assertSame(DefaultEncoder::class, $encoderDef->getClass());
        $this->assertEquals(new Reference('lexik_jwt_authentication.jws_provider.default'), $encoderDef->getArgument(0));
        $this->assertEquals(
            [
                new Reference('lexik_jwt_authentication.key_loader'),
                '%lexik_jwt_authentication.encoder.crypto_engine%',
                '%lexik_jwt_authentication.encoder.signature_algorithm%',
                '%lexik_jwt_authentication.token_ttl%',
                '%lexik_jwt_authentication.clock_skew%',
            ],
            $container->getDefinition('lexik_jwt_authentication.jws_provider.default')->getArguments()
        );
        $this->assertSame(
            ['private.pem', 'public.pem', '%lexik_jwt_authentication.pass_phrase%'],
            $container->findDefinition('lexik_jwt_authentication.key_loader')->getArguments()
        );
    }

    public function testTokenExtractorsConfiguration()
    {
        // Default configuration
        $chainTokenExtractor = $this->getContainer(['secret_key' => 'private.pem', 'public_key' => 'public.pem'])->getDefinition('lexik_jwt_authentication.extractor.chain_extractor');

        $extractorIds = array_map('strval', $chainTokenExtractor->getArgument(0));

        $this->assertContains('lexik_jwt_authentication.extractor.authorization_header_extractor', $extractorIds);
        $this->assertNotContains('lexik_jwt_authentication.extractor.cookie_extractor', $extractorIds);
        $this->assertNotContains('lexik_jwt_authentication.extractor.query_parameter_extractor', $extractorIds);

        // Custom configuration
        $chainTokenExtractor = $this->getContainer(['secret_key' => 'private.pem', 'public_key' => 'public.pem', 'token_extractors' => ['authorization_header' => true, 'cookie' => true]])
            ->getDefinition('lexik_jwt_authentication.extractor.chain_extractor');

        $extractorIds = array_map('strval', $chainTokenExtractor->getArgument(0));

        $this->assertContains('lexik_jwt_authentication.extractor.authorization_header_extractor', $extractorIds);
        $this->assertContains('lexik_jwt_authentication.extractor.cookie_extractor', $extractorIds);
        $this->assertNotContains('lexik_jwt_authentication.extractor.query_parameter_extractor', $extractorIds);
    }

    private function getContainer($config = [])
    {
        $container = new ContainerBuilder();
        $container->registerExtension(new SecurityExtension());
        $container->registerExtension(new LexikJWTAuthenticationExtension());
        $container->loadFromExtension('lexik_jwt_authentication', $config);

        $container->getCompilerPassConfig()->setOptimizationPasses([new ResolveChildDefinitionsPass()]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->compile();

        return $container;
    }
}
