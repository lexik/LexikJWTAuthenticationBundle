<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Command;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
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
#[AsCommand(name: 'lexik:jwt:generate-token', description: 'Generates a JWT token for a given user.')]
class GenerateTokenCommand extends Command
{
    /**
     * @deprecated
     */
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
            ->addArgument('username', InputArgument::REQUIRED, 'Username of user to be retreived from user provider')
            ->addOption('ttl', 't', InputOption::VALUE_REQUIRED, 'Ttl in seconds to be added to current time. If not provided, the ttl configured in the bundle will be used. Use 0 to generate token without exp')
            ->addOption('user-class', 'c', InputOption::VALUE_REQUIRED, 'Userclass is used to determine which user provider to use')
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
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

        $payload = [];

        if (null !== $input->getOption('ttl') && ((int) $input->getOption('ttl')) == 0) {
            $payload['exp'] = 0;
        } elseif (null !== $input->getOption('ttl') && ((int) $input->getOption('ttl')) > 0) {
            $payload['exp'] = time() + $input->getOption('ttl');
        }

        $token = $this->tokenManager->createFromPayload($user, $payload);

        $output->writeln([
            '',
            '<info>' . $token . '</info>',
            '',
        ]);

        return 0;
    }
}
