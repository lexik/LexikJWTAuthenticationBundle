<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * GenKeysCommand.
 *
 * @author zorn-v
 */
class GenKeysCommand extends Command
{
    /**
     * @var string
     */
    private $privateKey;

    /**
     * @var string
     */
    private $publicKey;

    /**
     * @var string
     */
    private $passphrase;

    /**
     * Constructor.
     *
     * @param string $privateKey
     * @param string $publicKey
     * @param string $passphrase
     */
    public function __construct($privateKey, $publicKey, $passphrase)
    {
        $this->privateKey = $privateKey;
        $this->publicKey  = $publicKey;
        $this->passphrase = $passphrase;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('lexik:jwt:gen-keys')
            ->setDescription('Generate JWT keys')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $fs->mkdir(dirname($this->privateKey));
        $fs->mkdir(dirname($this->publicKey));

        $pKeyRes = openssl_pkey_new([
            'digest_alg'         => 'sha512',
            'private_key_bits'   => 4096,
            'private_key_type'   => OPENSSL_KEYTYPE_RSA,
            'encrypt_key'        => true,
            'encrypt_key_cipher' => OPENSSL_CIPHER_AES_256_CBC,
        ]);
        openssl_pkey_export($pKeyRes, $privKeyData, $this->passphrase);
        $pubKeyData = openssl_pkey_get_details($pKeyRes);

        $fs->dumpFile($this->privateKey, $privKeyData);
        $fs->dumpFile($this->publicKey, $pubKeyData['key']);

        $output->writeln('<info>Keys successfully generated</>');
    }
}
