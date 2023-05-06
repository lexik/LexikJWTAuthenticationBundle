<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Command;

use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\KeyLoaderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
#[AsCommand(name: 'lexik:jwt:check-config', description: 'Checks that the bundle is properly configured.')]
final class CheckConfigCommand extends Command
{
    private KeyLoaderInterface $keyLoader;

    private string $signatureAlgorithm;

    public function __construct(KeyLoaderInterface $keyLoader, string $signatureAlgorithm)
    {
        $this->keyLoader = $keyLoader;
        $this->signatureAlgorithm = $signatureAlgorithm;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->keyLoader->loadKey(KeyLoaderInterface::TYPE_PRIVATE);
            // No public key for HMAC
            if (!str_contains($this->signatureAlgorithm, 'HS')) {
                $this->keyLoader->loadKey(KeyLoaderInterface::TYPE_PUBLIC);
            }
        } catch (\RuntimeException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return Command::FAILURE;
        }

        $output->writeln('<info>The configuration seems correct.</info>');

        return Command::SUCCESS;
    }
}
