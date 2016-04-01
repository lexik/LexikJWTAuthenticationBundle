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
        $keyLoader = $this->getContainer()->get('lexik_jwt_authentication.openssl_key_loader');

        try {
            $keyLoader->checkOpenSSLConfig();
        } catch (\RuntimeException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1;
        }

        $output->writeln('<info>OpenSSL configuration seems correct.</info>');

        return 0;
    }
}
