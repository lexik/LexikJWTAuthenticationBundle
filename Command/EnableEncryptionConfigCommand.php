<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Command;

use Jose\Bundle\JoseFramework\JoseFrameworkBundle;
use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Core\Algorithm;
use Jose\Component\Core\AlgorithmManagerFactory;
use Jose\Component\Core\JWK;
use Jose\Component\Core\JWKSet;
use Jose\Component\Encryption\Algorithm\ContentEncryptionAlgorithm;
use Jose\Component\Encryption\Algorithm\KeyEncryptionAlgorithm;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Encryption\JWELoader;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\JWSLoader;
use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\KeyLoaderInterface;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Symfony\Bundle\FrameworkBundle\Command\AbstractConfigCommand;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
#[AsCommand(name: 'lexik:jwt:enable-encryption', description: 'Enable Web-Token encryption support.')]
final class EnableEncryptionConfigCommand extends AbstractConfigCommand
{
    /**
     * @deprecated
     */
    protected static $defaultName = 'lexik:jwt:enable-encryption';

    /**
     * @var ?AlgorithmManagerFactory
     */
    private $algorithmManagerFactory;

    public function __construct(
        ?AlgorithmManagerFactory $algorithmManagerFactory = null
    ) {
        parent::__construct();

        $this->algorithmManagerFactory = $algorithmManagerFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName(static::$defaultName)
            ->setDescription('Enable Web-Token encryption support.')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the modification of the configuration, even if already set.')
        ;
    }

    public function isEnabled(): bool
    {
        return $this->algorithmManagerFactory !== null;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $force = $input->getOption('force');
        $this->checkRequirements();
        $io = new SymfonyStyle($input, $output);
        $io->title('Web-Token Encryption support');
        $io->info('This tool will help you enabling the encryption support for Web-Token');

        $algorithms = $this->algorithmManagerFactory->all();
        $availableKeyEncryptionAlgorithms = array_map(
            static function (Algorithm $algorithm): string {
                return $algorithm->name();
            },
            array_filter($algorithms, static function (Algorithm $algorithm): bool {
                return ($algorithm instanceof KeyEncryptionAlgorithm && $algorithm->name() !== 'dir');
            })
        );
        $availableContentEncryptionAlgorithms = array_map(
            static function (Algorithm $algorithm): string {
                return $algorithm->name();
            },
            array_filter($algorithms, static function (Algorithm $algorithm): bool {
                return $algorithm instanceof ContentEncryptionAlgorithm;
            })
        );

        $keyEncryptionAlgorithmAlias = $io->choice('Key Encryption Algorithm', $availableKeyEncryptionAlgorithms);
        $contentEncryptionAlgorithmAlias = $io->choice('Content Encryption Algorithm', $availableContentEncryptionAlgorithms);
        $keyEncryptionAlgorithm = $algorithms[$keyEncryptionAlgorithmAlias];
        $contentEncryptionAlgorithm = $algorithms[$contentEncryptionAlgorithmAlias];

        $continueOnDecryptionFailure = 'yes' === $io->choice('Continue decryption on failure', ['yes', 'no'], 'no');

        $extension = $this->findExtension('lexik_jwt_authentication');
        $config = $this->getConfiguration($extension);
        if (!isset($config['encoder']['service']) || $config['encoder']['service'] !== 'lexik_jwt_authentication.encoder.web_token') {
            $io->error('Please migrate to WebToken first.');
            return self::FAILURE;
        }
        if (!$force && ($config['access_token_issuance']['encryption']['enabled'] || $config['access_token_verification']['encryption']['enabled'])) {
            $io->error('Encryption support is already enabled.');
            return self::FAILURE;
        }

        $key = $this->generatePrivateKey($keyEncryptionAlgorithm);
        $keyset = $this->generatePublicKeyset($key, $keyEncryptionAlgorithm->name());

        $config['access_token_issuance']['encryption'] = [
            'enabled' => true,
            'key_encryption_algorithm' => $keyEncryptionAlgorithm->name(),
            'content_encryption_algorithm' => $contentEncryptionAlgorithm->name(),
            'key' => json_encode($key, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ];
        $config['access_token_verification']['encryption'] = [
            'enabled' => true,
            'continue_on_decryption_failure' => $continueOnDecryptionFailure,
            'header_checkers' => ['iat_with_clock_skew', 'nbf_with_clock_skew', 'exp_with_clock_skew'],
            'allowed_key_encryption_algorithms' => [$keyEncryptionAlgorithm->name()],
            'allowed_content_encryption_algorithms' => [$contentEncryptionAlgorithm->name()],
            'keyset' => json_encode($keyset, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ];

        $io->comment('Please replace the current configuration with the following parameters.');
        $io->section('# config/packages/lexik_jwt_authentication.yaml');
        $io->writeln(Yaml::dump([$extension->getAlias() => $config], 10));
        $io->section('# End of file');

        return self::SUCCESS;
    }

    private function generatePublicKeyset(JWK $key, string $algorithm): JWKSet
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

    private function generatePrivateKey(KeyEncryptionAlgorithm $algorithm): JWK
    {
        $keyType = current($algorithm->allowedKeyTypes());
        switch ($keyType) {
            case 'oct':
                return $this->createOctKey($this->getKeySize($algorithm->name()), $algorithm->name());
            case 'OKP':
                return $this->createOkpKey('X25519', $algorithm->name());
            case 'EC':
                return $this->createEcKey('P-256', $algorithm->name());
            case 'RSA':
                return $this->createRsaKey($this->getKeySize($algorithm->name()), $algorithm->name());
            default:
                throw new \InvalidArgumentException('Unsupported key type.');
        }
    }

    private function checkRequirements(): void
    {
        $requirements = [
            JoseFrameworkBundle::class => 'web-token/jwt-bundle',
            JWKFactory::class => 'web-token/jwt-key-mgmt',
            ClaimCheckerManager::class => 'web-token/jwt-checker',
            JWEBuilder::class => 'web-token/jwt-encryption',
        ];
        if ($this->algorithmManagerFactory === null) {
            throw new \RuntimeException('The package "web-token/jwt-bundle" is missing. Please install it for using this migration tool.');
        }
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
            case 'RSA1_5':
            case 'RSA-OAEP':
            case 'RSA-OAEP-256':
                return 4096;
            case 'A128KW':
            case 'A128GCMKW':
            case 'PBES2-HS256+A128KW':
                return 128;
            case 'A192KW':
            case 'A192GCMKW':
            case 'PBES2-HS384+A192KW':
                return 192;
            case 'A256KW':
            case 'A256GCMKW':
            case 'PBES2-HS512+A256KW':
                return 256;
            default:
                throw new \LogicException('Unsupported algorithm');
        }
    }

    private function getOptions(string $algorithm): array
    {
        return [
            'use' => 'enc',
            'alg' => $algorithm,
            'kid' => Base64UrlSafe::encodeUnpadded(random_bytes(16))
        ];
    }
}
