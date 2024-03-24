<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Command;

use Jose\Bundle\JoseFramework\JoseFrameworkBundle;
use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Core\JWK;
use Jose\Component\Core\JWKSet;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\JWSLoader;
use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\KeyLoaderInterface;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Symfony\Bundle\FrameworkBundle\Command\AbstractConfigCommand;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Compiler\ValidateEnvPlaceholdersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Florent Morselli <florent.morselli@spomky-labs.com>
 */
#[AsCommand(name: 'lexik:jwt:migrate-config', description: 'Migrate LexikJWTAuthenticationBundle configuration to the Web-Token one.')]
final class MigrateConfigCommand extends AbstractConfigCommand
{
    /**
     * @deprecated
     */
    protected static $defaultName = 'lexik:jwt:migrate-config';

    /**
     * @var KeyLoaderInterface
     */
    private $keyLoader;

    /**
     * @var string
     */
    private $signatureAlgorithm;

    /**
     * @var string
     */
    private $passphrase;

    public function __construct(
        KeyLoaderInterface $keyLoader,
        string $passphrase,
        string $signatureAlgorithm
    ) {
        parent::__construct();
        $this->keyLoader = $keyLoader;
        $this->passphrase = $passphrase === '' ? null : $passphrase;
        $this->signatureAlgorithm = $signatureAlgorithm;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName(static::$defaultName)
            ->setDescription('Migrate the configuration to Web-Token')
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->checkRequirements();
        $io = new SymfonyStyle($input, $output);
        $io->title('Web-Token Migration tool');
        $io->info('This tool will help you converting the current LexikJWTAuthenticationBundle configuration to support Web-Token');

        try {
            $key = $this->getKey();
            $keyset = $this->getKeyset($key, $this->signatureAlgorithm);
        } catch (\RuntimeException $e) {
            $io->error('An error occurred: ' . $e->getMessage());

            return self::FAILURE;
        }

        $extension = $this->findExtension('lexik_jwt_authentication');
        $config = $this->getConfiguration($extension);

        foreach ($config['set_cookies'] as $cookieConfig) {
            if ($cookieConfig['split'] !== []) {
                $io->error('Web-Token is not compatible with the cookie split feature. Please disable this option before using this migration tool.');

                return self::FAILURE;
            }
        }

        $config['encoder'] = ['service' => 'lexik_jwt_authentication.encoder.web_token'];
        $config['access_token_issuance'] = [
            'enabled' => true,
            'signature' => [
                'signature_algorithm' => $this->signatureAlgorithm,
                'key' => json_encode($key, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ]
        ];
        $config['access_token_verification'] = [
            'enabled' => true,
            'signature' => [
                'allowed_signature_algorithms' => [$this->signatureAlgorithm],
                'keyset' => json_encode($keyset, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ]
        ];

        $io->comment('Please replace the current configuration with the following parameters.');
        $io->section('# config/packages/lexik_jwt_authentication.yaml');
        $io->writeln(Yaml::dump([$extension->getAlias() => $config], 10));
        $io->section('# End of file');

        return self::SUCCESS;
    }

    private function getKeyset(JWK $key, string $algorithm): JWKSet
    {
        $keyset = new JWKSet([$key->toPublic()]);
        switch ($key->get('kty')) {
            case 'oct':
                return $this->withOctKeys($keyset, $algorithm);
            case 'OKP':
                return $this->withOkpKeys($keyset, $algorithm, $key->get('crv'));
            case 'EC':
                return $this->withEcKeys($keyset, $algorithm, $key->get('crv'));
            case 'RSA':
                return $this->withRsaKeys($keyset, $algorithm);
            default:
                throw new \InvalidArgumentException('Unsupported key type.');
        }
    }

    private function withOctKeys(JWKSet $keyset, string $algorithm): JWKSet
    {
        $size = $this->getKeySize($algorithm);

        return $keyset
            ->with($this->createOctKey($size, $algorithm)->toPublic())
            ->with($this->createOctKey($size, $algorithm)->toPublic())
        ;
    }

    private function withRsaKeys(JWKSet $keyset, string $algorithm): JWKSet
    {
        return $keyset
            ->with($this->createRsaKey(2048, $algorithm)->toPublic())
            ->with($this->createRsaKey(2048, $algorithm)->toPublic())
        ;
    }

    private function withOkpKeys(JWKSet $keyset, string $algorithm, string $curve): JWKSet
    {
        return $keyset
            ->with($this->createOkpKey($curve, $algorithm)->toPublic())
            ->with($this->createOkpKey($curve, $algorithm)->toPublic())
        ;
    }

    private function withEcKeys(JWKSet $keyset, string $algorithm, string $curve): JWKSet
    {
        return $keyset
            ->with($this->createEcKey($curve, $algorithm)->toPublic())
            ->with($this->createEcKey($curve, $algorithm)->toPublic())
        ;
    }

    private function getKey(): JWK
    {
        $additionalValues = [
            'use' => 'sig',
            'alg' => $this->signatureAlgorithm,
        ];
        // No public key for HMAC
        if (false !== strpos($this->signatureAlgorithm, 'HS')) {
            return JWKFactory::createFromSecret(
                $this->keyLoader->loadKey(KeyLoaderInterface::TYPE_PUBLIC),
                $additionalValues
            );
        }
        return JWKFactory::createFromKey(
            $this->keyLoader->loadKey(KeyLoaderInterface::TYPE_PRIVATE),
            $this->passphrase,
            $additionalValues
        );
    }

    private function checkRequirements(): void
    {
        $requirements = [
            JoseFrameworkBundle::class => 'web-token/jwt-bundle',
            JWKFactory::class => 'web-token/jwt-key-mgmt',
            ClaimCheckerManager::class => 'web-token/jwt-checker',
            JWSBuilder::class => 'web-token/jwt-signature',
        ];

        foreach (array_keys($requirements) as $requirement) {
            if (!class_exists($requirement)) {
                throw new \RuntimeException(sprintf('The package "%s" is missing. Please install it for using this migration tool.', $requirement));
            }
        }
    }
    private function getConfiguration(ExtensionInterface $extension): array
    {
        $container = $this->compileContainer();

        $config = $this->getConfig($extension, $container);
        $uselessParameters = ['secret_key', 'public_key', 'pass_phrase', 'private_key_path', 'public_key_path', 'additional_public_keys'];
        foreach ($uselessParameters as $parameter) {
            unset($config[$parameter]);
        }

        return $config;
    }

    private function createOctKey(int $size, string $algorithm): JWK
    {
        return JWKFactory::createOctKey($size, $this->getOptions($algorithm));
    }

    private function createRsaKey(int $size, string $algorithm): JWK
    {
        return JWKFactory::createRSAKey($size, $this->getOptions($algorithm));
    }

    private function createOkpKey(string $curve, string $algorithm): JWK
    {
        return JWKFactory::createOKPKey($curve, $this->getOptions($algorithm));
    }

    private function createEcKey(string $curve, string $algorithm): JWK
    {
        return JWKFactory::createECKey($curve, $this->getOptions($algorithm));
    }

    private function compileContainer(): ContainerBuilder
    {
        $kernel = clone $this->getApplication()->getKernel();
        $kernel->boot();

        $method = new \ReflectionMethod($kernel, 'buildContainer');
        $container = $method->invoke($kernel);
        $container->getCompiler()->compile($container);

        return $container;
    }

    private function getConfig(ExtensionInterface $extension, ContainerBuilder $container)
    {
        return $container->resolveEnvPlaceholders(
            $container->getParameterBag()->resolveValue(
                $this->getConfigForExtension($extension, $container)
            )
        );
    }

    private function getConfigForExtension(ExtensionInterface $extension, ContainerBuilder $container): array
    {
        $extensionAlias = $extension->getAlias();

        $extensionConfig = [];
        foreach ($container->getCompilerPassConfig()->getPasses() as $pass) {
            if ($pass instanceof ValidateEnvPlaceholdersPass) {
                $extensionConfig = $pass->getExtensionConfig();
                break;
            }
        }

        if (isset($extensionConfig[$extensionAlias])) {
            return $extensionConfig[$extensionAlias];
        }

        // Fall back to default config if the extension has one

        if (!$extension instanceof ConfigurationExtensionInterface) {
            throw new \LogicException(sprintf('The extension with alias "%s" does not have configuration.', $extensionAlias));
        }

        $configs = $container->getExtensionConfig($extensionAlias);
        $configuration = $extension->getConfiguration($configs, $container);
        $this->validateConfiguration($extension, $configuration);

        return (new Processor())->processConfiguration($configuration, $configs);
    }

    private function getKeySize(string $algorithm): int
    {
        switch ($algorithm) {
            case 'HS256':
            case 'HS256/64':
                return 256;
            case 'HS384':
                return 384;
            case 'HS512':
                return 512;
            default:
                throw new \LogicException('Unsupported algorithm');
        }
    }

    private function getOptions(string $algorithm): array
    {
        return [
            'use' => 'sig',
            'alg' => $algorithm,
            'kid' => Base64UrlSafe::encodeUnpadded(random_bytes(16))
        ];
    }
}
