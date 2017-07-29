<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader;

/**
 * Reads crypto keys.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class RawKeyLoader extends AbstractKeyLoader implements KeyDumperInterface
{
    /**
     * @param string $type
     *
     * @return string
     *
     * @throws \RuntimeException If the key cannot be read
     */
    public function loadKey($type)
    {
        if (!in_array($type, [self::TYPE_PUBLIC, self::TYPE_PRIVATE])) {
            throw new \InvalidArgumentException(sprintf('The key type must be "public" or "private", "%s" given.', $type));
        }

        if (self::TYPE_PUBLIC === $type) {
            return $this->dumpKey();
        }

        return $this->getSigningKey();
    }

    /**
     * {@inheritdoc}
     */
    public function dumpKey()
    {
        if ($publicKey = $this->getPublicKey()) {
            return $publicKey;
        }

        $signingKey = $this->getSigningKey();

        // no public key provided, compute it from signing key
        try {
            $publicKey  = openssl_pkey_get_details(openssl_pkey_get_private($signingKey, $this->getPassphrase()))['key'];
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                'Secret key either does not exist, is not readable or is invalid. Did you correctly set the "lexik_jwt_authentication.secret_key" config option?'
            );
        }

        return $publicKey;
    }
}
