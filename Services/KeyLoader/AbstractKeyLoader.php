<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader;

/**
 * Abstract class for key loaders.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 *
 * @internal
 */
abstract class AbstractKeyLoader implements KeyLoaderInterface
{
    private $signingKey;
    private $publicKey;
    private $passphrase;
    private $additionalPublicKeys;

    public function __construct(?string $signingKey = null, ?string $publicKey = null, ?string $passphrase = null, array $additionalPublicKeys = [])
    {
        $this->signingKey = $signingKey;
        $this->publicKey = $publicKey;
        $this->passphrase = $passphrase;
        $this->additionalPublicKeys = $additionalPublicKeys;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassphrase()
    {
        return $this->passphrase;
    }

    public function getSigningKey()
    {
        return $this->signingKey && is_file($this->signingKey) ? $this->readKey(self::TYPE_PRIVATE) : $this->signingKey;
    }

    public function getPublicKey()
    {
        return $this->publicKey && is_file($this->publicKey) ? $this->readKey(self::TYPE_PUBLIC) : $this->publicKey;
    }

    public function getAdditionalPublicKeys(): array
    {
        $contents = [];

        foreach ($this->additionalPublicKeys as $key) {
            if (!$key) {
                continue;
            }
            if (is_file($key) && !is_readable($key)) {
                throw new \RuntimeException(sprintf('Additional public key "%s" is not readable. Did you correctly set the "lexik_jwt_authentication.additional_public_keys" configuration key?', $key));
            }

            $contents[] = is_file($key) ? file_get_contents($key) : $key;
        }

        return $contents;
    }

    /**
     * @param string $type One of "public" or "private"
     *
     * @return string The path of the key, an empty string if not a valid path
     *
     * @throws \InvalidArgumentException If the given type is not valid
     * @throws \InvalidArgumentException If the given type is not valid
     */
    protected function getKeyPath($type)
    {
        if (!in_array($type, [self::TYPE_PUBLIC, self::TYPE_PRIVATE])) {
            throw new \InvalidArgumentException(sprintf('The key type must be "public" or "private", "%s" given.', $type));
        }

        $path = self::TYPE_PUBLIC === $type ? $this->publicKey : $this->signingKey;

        if (!is_file($path) || !is_readable($path)) {
            throw new \RuntimeException(sprintf('%s key is not a file or is not readable.', ucfirst($type)));
        }

        return $path;
    }

    private function readKey($type)
    {
        $isPublic = self::TYPE_PUBLIC === $type;
        $key = $isPublic ? $this->publicKey : $this->signingKey;

        if (!$key || !is_file($key) || !is_readable($key)) {
            if ($isPublic) {
                return null;
            }

            throw new \RuntimeException(sprintf('Signature key "%s" does not exist or is not readable. Did you correctly set the "lexik_jwt_authentication.signature_key" configuration key?', $key));
        }

        return file_get_contents($key);
    }
}
