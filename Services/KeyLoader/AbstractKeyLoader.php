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
    private ?string $signingKey;
    private ?string $publicKey;
    private ?string $passphrase;
    private array $additionalPublicKeys;

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
    public function getPassphrase(): ?string
    {
        return $this->passphrase;
    }

    public function getSigningKey(): ?string
    {
        return $this->signingKey && is_file($this->signingKey) ? $this->readKey(self::TYPE_PRIVATE) : $this->signingKey;
    }

    public function getPublicKey(): ?string
    {
        return $this->publicKey && is_file($this->publicKey) ? $this->readKey(self::TYPE_PUBLIC) : $this->publicKey;
    }

    public function getAdditionalPublicKeys(): array
    {
        $contents = [];

        foreach ($this->additionalPublicKeys as $key) {
            if (!$key || !is_file($key) || !is_readable($key)) {
                throw new \RuntimeException(sprintf('Additional public key "%s" does not exist or is not readable. Did you correctly set the "lexik_jwt_authentication.additional_public_keys" configuration key?', $key));
            }

            $rawKey = @file_get_contents($key);

            if (false === $rawKey) {
                // Try invalidating the realpath cache
                clearstatcache(true, $key);
                $rawKey = file_get_contents($key);
            }
            $contents[] = $rawKey;
        }

        return $contents;
    }

    private function readKey($type): ?string
    {
        $isPublic = self::TYPE_PUBLIC === $type;
        $key = $isPublic ? $this->publicKey : $this->signingKey;

        if (!$key || !is_file($key) || !is_readable($key)) {
            if ($isPublic) {
                return null;
            }

            throw new \RuntimeException(sprintf('Signature key "%s" does not exist or is not readable. Did you correctly set the "lexik_jwt_authentication.signature_key" configuration key?', $key));
        }

        $rawKey = @file_get_contents($key);

        if (false === $rawKey) {
            // Try invalidating the realpath cache
            clearstatcache(true, $key);
            $rawKey = file_get_contents($key);
        }

        return $rawKey;
    }
}
