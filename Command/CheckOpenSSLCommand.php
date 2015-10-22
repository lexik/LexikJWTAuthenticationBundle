<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CheckOpenSSLCommand
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class CheckOpenSSLCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('lexik:jwt:check-open-ssl')
            ->setDescription('Check JWT configuration is correct');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->checkOpenSSLConfig(
                $this->getContainer()->getParameter('lexik_jwt_authentication.private_key_path'),
                $this->getContainer()->getParameter('lexik_jwt_authentication.public_key_path'),
                $this->getContainer()->getParameter('lexik_jwt_authentication.pass_phrase')
            );
        } catch (\RuntimeException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1;
        }

        $output->writeln('<info>OpenSSL configuration seems correct.</info>');
        return 0;
    }

    /**
     * Checks that configured keys exists and private key can be parsed using the passphrase
     *
     * @param string $privateKey
     * @param string $publicKey
     * @param string $passphrase
     *
     * @throws \RuntimeException
     */
    public function checkOpenSSLConfig($privateKey, $publicKey, $passphrase)
    {
        if (!file_exists($privateKey) || !is_readable($privateKey)) {
            throw new \RuntimeException(sprintf(
                'Private key "%s" does not exist or is not readable.',
                $privateKey
            ));
        }

        if (!file_exists($publicKey) || !is_readable($publicKey)) {
            throw new \RuntimeException(sprintf(
                'Public key "%s" does not exist or is not readable.',
                $publicKey
            ));
        }

        if (!openssl_pkey_get_private('file://' . $privateKey, $passphrase)) {
            throw new \RuntimeException(sprintf(
                'Failed to open private key "%s". Did you correctly configure the corresponding passphrase?',
                $privateKey
            ));
        }
    }
}
