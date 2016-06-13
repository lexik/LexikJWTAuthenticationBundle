<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CheckConfigCommand.
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class CheckConfigCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('lexik:jwt:check-config')
            ->setDescription('Check JWT configuration');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $keyLoader = $this->getContainer()->get('lexik_jwt_authentication.key_loader');

        try {
            $keyLoader->loadKey('public');
            $keyLoader->loadKey('private');
        } catch (\RuntimeException $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');

            return 1;
        }

        $output->writeln('<info>The configuration seems correct.</info>');

        return 0;
    }
}
