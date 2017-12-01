<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

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

        $process = new Process(sprintf('openssl genrsa -passout env:JWT_PASSPHRASE -out %s -aes256 4096', escapeshellarg($this->privateKey)));
        if (method_exists($process, 'inheritEnvironmentVariables')) {
            $process->inheritEnvironmentVariables(true); //prevent symfony deprecation notice
        }
        $process->setEnv(['JWT_PASSPHRASE'=>$this->passphrase]);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $process->setCommandLine(sprintf('openssl rsa -passin env:JWT_PASSPHRASE -pubout -in %s -out %s', escapeshellarg($this->privateKey), escapeshellarg($this->publicKey)));
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output->writeln('<info>Keys successfully generated</>');
    }
}
