<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\DependencyInjection;

use Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\LexikJWTAuthenticationExtension;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\LcobucciJWTEncoder;
use Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle;
use Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\TestCase;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Tests the bundle extension and the configuration of services.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class LexikJWTAuthenticationExtensionTest extends TestCase
{
    private static $resourceDir;

    protected function setUp()
    {
        static::bootKernel();
        self::$resourceDir = sys_get_temp_dir().'/LexikJWTAuthenticationBundle/';

        if (!is_dir(self::$resourceDir)) {
            (new Filesystem())->mkdir(self::$resourceDir);
        }
    }

    public function testEncoderAlias()
    {
        $this->assertInstanceOf(LcobucciJWTEncoder::class, static::$kernel->getContainer()->get('lexik_jwt_authentication.encoder'));
    }

    /**
     * @group legacy
     * @expectedDeprecation Using "lexik_jwt_authentication.encoder.default" as encoder service is deprecated since LexikJWTAuthenticationBundle 2.5, use "lexik_jwt_authentication.encoder.lcobucci" (default) or your own encoder service instead.
     */
    public function testDeprecatedDefaultEncoderService()
    {
        (new LexikJWTAuthenticationExtension())->load([['encoder' => ['service' => 'lexik_jwt_authentication.encoder.default']]], new ContainerBuilder());
    }

    public function testTokenExtractorsConfiguration()
    {
        // Default configuration
        $this->dumpConfig('token_extractors', []);
        $chainTokenExtractor = $this->getContainer('token_extractors')->getDefinition('lexik_jwt_authentication.extractor.chain_extractor');

        $extractorIds = array_map(function ($ref) {
            return (string) $ref;
        }, $chainTokenExtractor->getArgument(0));

        $this->assertContains('lexik_jwt_authentication.extractor.authorization_header_extractor', $extractorIds);
        $this->assertNotContains('lexik_jwt_authentication.extractor.cookie_extractor', $extractorIds);
        $this->assertNotContains('lexik_jwt_authentication.extractor.query_parameter_extractor', $extractorIds);

        // Custom configuration
        $this->dumpConfig('token_extractors', ['token_extractors' => ['authorization_header' => true, 'cookie' => true]]);
        $chainTokenExtractor = $this->getContainer('token_extractors')->getDefinition('lexik_jwt_authentication.extractor.chain_extractor');

        $extractorIds = array_map(function ($ref) {
            return (string) $ref;
        }, $chainTokenExtractor->getArgument(0));

        $this->assertContains('lexik_jwt_authentication.extractor.authorization_header_extractor', $extractorIds);
        $this->assertContains('lexik_jwt_authentication.extractor.cookie_extractor', $extractorIds);
        $this->assertNotContains('lexik_jwt_authentication.extractor.query_parameter_extractor', $extractorIds);
    }

    private function getContainer($file)
    {
        $container = new ContainerBuilder();
        $container->registerExtension(new SecurityExtension());
        $container->registerExtension(new LexikJWTAuthenticationExtension());

        (new LexikJWTAuthenticationBundle())->build($container);
        (new YamlFileLoader($container, new FileLocator([self::$resourceDir])))->load($file.'.yml');

        $container->getCompilerPassConfig()->setOptimizationPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->compile();

        return $container;
    }

    private function dumpConfig($filename, array $configs = [])
    {
        file_put_contents(self::$resourceDir.$filename.'.yml', @Yaml::dump(['lexik_jwt_authentication' => $configs]));
    }

    public static function tearDownAfterClass()
    {
        (new Filesystem())->remove(self::$resourceDir);
    }
}
