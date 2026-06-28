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

        // Detect EdDSA key: base64-encoded sodium secretkey is exactly SODIUM_CRYPTO_SIGN_SECRETKEYBYTES when decoded
        $decoded = base64_decode($signingKey, true);
        if (false !== $decoded && strlen($decoded) === SODIUM_CRYPTO_SIGN_SECRETKEYBYTES) {
            return base64_encode(sodium_crypto_sign_publickey_from_secretkey($decoded));
        }

        // no public key provided, compute it from signing key using OpenSSL
        try {
            $publicKey = openssl_pkey_get_details(openssl_pkey_get_private($signingKey, $this->getPassphrase()))['key'];
        } catch (\Throwable $e) {
            throw new \RuntimeException('Secret key either does not exist, is not readable or is invalid. Did you correctly set the "lexik_jwt_authentication.secret_key" config option?');
        }

        return $publicKey;
    }
}
