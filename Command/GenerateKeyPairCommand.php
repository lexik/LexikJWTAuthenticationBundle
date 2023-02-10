<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Beno!t POLASZEK <bpolaszek@gmail.com>
 */
#[AsCommand(name: self::NAME, description: 'Generate public/private keys for use in your application.')]
final class GenerateKeyPairCommand extends Command
{
    private const NAME = 'lexik:jwt:generate-keypair';

    private const ACCEPTED_ALGORITHMS = [
        'RS256',
        'RS384',
        'RS512',
        'HS256',
        'HS384',
        'HS512',
        'ES256',
        'ES384',
        'ES512',
    ];

    private Filesystem $filesystem;

    private ?string $secretKey;

    private ?string $publicKey;

    private ?string $passphrase;

    private string $algorithm;

    public function __construct(Filesystem $filesystem, ?string $secretKey, ?string $publicKey, ?string $passphrase, string $algorithm)
    {
        $this->filesystem = $filesystem;
        $this->secretKey = $secretKey;
        $this->publicKey = $publicKey;
        $this->passphrase = $passphrase;
        $this->algorithm = $algorithm;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do not update key files.');
        $this->addOption('skip-if-exists', null, InputOption::VALUE_NONE, 'Do not update key files if they already exist.');
        $this->addOption('overwrite', null, InputOption::VALUE_NONE, 'Overwrite key files if they already exist.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!in_array($this->algorithm, self::ACCEPTED_ALGORITHMS, true)) {
            $io->error(sprintf('Cannot generate key pair with the provided algorithm `%s`.', $this->algorithm));

            return Command::FAILURE;
        }

        [$secretKey, $publicKey] = $this->generateKeyPair($this->passphrase);

        if (true === $input->getOption('dry-run')) {
            $io->success('Your keys have been generated!');
            $io->newLine();
            $io->writeln(sprintf('Update your private key in <info>%s</info>:', $this->secretKey));
            $io->writeln($secretKey);
            $io->newLine();
            $io->writeln(sprintf('Update your public key in <info>%s</info>:', $this->publicKey));
            $io->writeln($publicKey);

            return Command::SUCCESS;
        }

        if (null === $this->secretKey || null === $this->publicKey) {
            throw new LogicException(sprintf('The "lexik_jwt_authentication.secret_key" and "lexik_jwt_authentication.public_key" config options must not be empty for using the "%s" command.', self::NAME));
        }

        $alreadyExists = $this->filesystem->exists($this->secretKey) || $this->filesystem->exists($this->publicKey);

        if ($alreadyExists) {
            try {
                $this->handleExistingKeys($input);
            } catch (\RuntimeException $e) {
                if (0 === $e->getCode()) {
                    $io->comment($e->getMessage());

                    return Command::SUCCESS;
                }

                $io->error($e->getMessage());

                return Command::FAILURE;
            }

            if (!$io->confirm('You are about to replace your existing keys. Are you sure you wish to continue?')) {
                $io->comment('Your action was canceled.');

                return Command::SUCCESS;
            }
        }

        $this->filesystem->dumpFile($this->secretKey, $secretKey);
        $this->filesystem->dumpFile($this->publicKey, $publicKey);

        $io->success('Done!');

        return Command::SUCCESS;
    }

    private function handleExistingKeys(InputInterface $input): void
    {
        if (true === $input->getOption('skip-if-exists') && true === $input->getOption('overwrite')) {
            throw new \RuntimeException('Both options `--skip-if-exists` and `--overwrite` cannot be combined.', 1);
        }

        if (true === $input->getOption('skip-if-exists')) {
            throw new \RuntimeException('Your key files already exist, they won\'t be overriden.', 0);
        }

        if (false === $input->getOption('overwrite')) {
            throw new \RuntimeException('Your keys already exist. Use the `--overwrite` option to force regeneration.', 1);
        }
    }

    private function generateKeyPair(?string $passphrase): array
    {
        $config = $this->buildOpenSSLConfiguration();

        $resource = \openssl_pkey_new($config);
        if (false === $resource) {
            throw new \RuntimeException(\openssl_error_string());
        }

        $success = \openssl_pkey_export($resource, $privateKey, $passphrase);

        if (false === $success) {
            throw new \RuntimeException(\openssl_error_string());
        }

        $publicKeyData = \openssl_pkey_get_details($resource);

        if (false === $publicKeyData) {
            throw new \RuntimeException(\openssl_error_string());
        }

        $publicKey = $publicKeyData['key'];

        return [$privateKey, $publicKey];
    }

    private function buildOpenSSLConfiguration(): array
    {
        $digestAlgorithms = [
            'RS256' => 'sha256',
            'RS384' => 'sha384',
            'RS512' => 'sha512',
            'HS256' => 'sha256',
            'HS384' => 'sha384',
            'HS512' => 'sha512',
            'ES256' => 'sha256',
            'ES384' => 'sha384',
            'ES512' => 'sha512',
        ];
        $privateKeyBits = [
            'RS256' => 2048,
            'RS384' => 2048,
            'RS512' => 4096,
            'HS256' => 512,
            'HS384' => 512,
            'HS512' => 512,
            'ES256' => 384,
            'ES384' => 512,
            'ES512' => 1024,
        ];
        $privateKeyTypes = [
            'RS256' => \OPENSSL_KEYTYPE_RSA,
            'RS384' => \OPENSSL_KEYTYPE_RSA,
            'RS512' => \OPENSSL_KEYTYPE_RSA,
            'HS256' => \OPENSSL_KEYTYPE_DH,
            'HS384' => \OPENSSL_KEYTYPE_DH,
            'HS512' => \OPENSSL_KEYTYPE_DH,
            'ES256' => \OPENSSL_KEYTYPE_EC,
            'ES384' => \OPENSSL_KEYTYPE_EC,
            'ES512' => \OPENSSL_KEYTYPE_EC,
        ];

        $curves = [
            'ES256' => 'secp256k1',
            'ES384' => 'secp384r1',
            'ES512' => 'secp521r1',
        ];

        $config = [
            'digest_alg' => $digestAlgorithms[$this->algorithm],
            'private_key_type' => $privateKeyTypes[$this->algorithm],
            'private_key_bits' => $privateKeyBits[$this->algorithm],
        ];

        if (isset($curves[$this->algorithm])) {
            $config['curve_name'] = $curves[$this->algorithm];
        }

        return $config;
    }
}
