<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Command;

use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\KeyLoaderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CheckConfigCommand.
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class CheckConfigCommand extends Command
{
    protected static $defaultName = 'lexik:jwt:check-config';

    private $keyLoader;

    private $signatureAlgorithm;

    public function __construct(KeyLoaderInterface $keyLoader, $signatureAlgorithm)
    {
        $this->keyLoader = $keyLoader;
        $this->signatureAlgorithm = $signatureAlgorithm;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(static::$defaultName)
            ->setDescription('Checks JWT configuration');
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
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
