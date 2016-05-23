<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader;

/**
 * Load configuration keys.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
abstract class AbstractKeyLoader implements KeyLoaderInterface
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
    }

    /**
     * {@inheritdoc}
     */
    public function getPassphrase()
    {
        return $this->passphrase;
    }

    /**
     * @param string $type One of "public" or "private"
     *
     * @return string The path of the key
     *
     * @throws \InvalidArgumentException If the given type is not valid.
     */
    protected function getKeyPath($type)
    {
        if ($type === 'public') {
            return $this->publicKey;
        }

        if ($type === 'private') {
            return $this->privateKey;
        }

        throw new \InvalidArgumentException(sprintf('The key type must be "public" or "private", "%s" given.', $type));
    }

    /**
     * @param string $type The key type
     * @param string $path The key path
     *
     * @throws \RuntimeException
     */
    protected function createUnreadableKeyException($type, $path)
    {
        return new \RuntimeException(
            sprintf('%s key "%s" does not exist or is not readable. Did you correctly set the "lexik_jwt_authentication.jwt_%s_key_path" config option?', ucfirst($type), $path, $type)
        );
    }
}
