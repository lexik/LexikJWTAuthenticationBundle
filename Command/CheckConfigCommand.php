<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Command;

use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\KeyLoaderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 *
 * @final
 */
#[AsCommand(name: 'lexik:jwt:check-config', description: 'Checks that the bundle is properly configured.')]
class CheckConfigCommand extends Command
{
    /**
     * @deprecated
     */
    protected static $defaultName = 'lexik:jwt:check-config';

    private $keyLoader;

    private $signatureAlgorithm;

    public function __construct(KeyLoaderInterface $keyLoader, $signatureAlgorithm)
    {
        $this->keyLoader = $keyLoader;
        $this->signatureAlgorithm = $signatureAlgorithm;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName(static::$defaultName)
            ->setDescription('Checks JWT configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->keyLoader->loadKey(KeyLoaderInterface::TYPE_PRIVATE);
            // No public key for HMAC
            if (false === strpos($this->signatureAlgorithm, 'HS')) {
                $this->keyLoader->loadKey(KeyLoaderInterface::TYPE_PUBLIC);
            }
        } catch (\RuntimeException $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');

            return 1;
        }

        $output->writeln('<info>The configuration seems correct.</info>');

        return 0;
    }
}
