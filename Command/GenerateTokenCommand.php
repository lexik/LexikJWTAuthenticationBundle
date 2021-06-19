<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Command;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * GenerateTokenCommand.
 *
 * @author Samuel Roze <samuel.roze@gmail.com>
 */
class GenerateTokenCommand extends Command
{
    protected static $defaultName = 'lexik:jwt:generate-token';

    private $tokenManager;

    /** @var \Traversable|UserProviderInterface[] */
    private $userProviders;

    public function __construct(JWTTokenManagerInterface $tokenManager, \Traversable $userProviders)
    {
        parent::__construct();

        $this->tokenManager = $tokenManager;
        $this->userProviders = $userProviders;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(static::$defaultName)
            ->setDescription('Generates a JWT token')
            ->addArgument('username', InputArgument::REQUIRED)
            ->addOption('user-class', 'c', InputOption::VALUE_REQUIRED)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->userProviders instanceof \Countable && 0 === \count($this->userProviders)) {
            throw new \RuntimeException('You must have at least 1 configured user provider to generate a token.');
        }

        if (!$userClass = $input->getOption('user-class')) {
            if (1 < \count($userProviders = iterator_to_array($this->userProviders))) {
                throw new \RuntimeException('The "--user-class" option must be passed as there is more than 1 configured user provider.');
            }

            $userProvider = current($userProviders);
        } else {
            $userProvider = null;

            foreach ($this->userProviders as $provider) {
                if ($provider->supportsClass($userClass)) {
                    $userProvider = $provider;

                    break;
                }
            }

            if (!$userProvider) {
                throw new \RuntimeException(sprintf('There is no configured user provider for class "%s".', $userClass));
            }
        }

        if (method_exists($userProvider, 'loadUserByIdentifier')) {
            $user = $userProvider->loadUserByIdentifier($input->getArgument('username'));
        } else {
            $user = $userProvider->loadUserByUsername($input->getArgument('username'));
        }

        $token = $this->tokenManager->create($user);

        $output->writeln([
            '',
            '<info>'.$token.'</info>',
            '',
        ]);

        return 0;
    }
}
