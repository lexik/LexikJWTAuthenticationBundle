<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader;

/**
 * Load config keys for the OpenSSL encryption engine.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class OpenSSLKeyLoader extends AbstractKeyLoader
{
    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException If the key cannot be read.
     * @throws \RuntimeException Either the key or the passphrase is not valid.
     */
    public function loadKey($type)
    {
        $path = $this->getKeyPath($type);

        if (!file_exists($path) || !is_readable($path)) {
            throw $this->createUnreadableKeyException($type, $path);
        }

        $encryptedKey = file_get_contents($path);
        $key = call_user_func_array(
            sprintf('openssl_pkey_get_%s', $type),
            $type == 'private' ? [$encryptedKey, $this->passphrase] : [$encryptedKey]
        );

        if (!$key) {
            throw new \RuntimeException(
                sprintf('Failed to load %1$s key "%2$s". Did you correctly set the "lexik_jwt_authentication.jwt_%1$s_key_path" config option?', $type, $path)
            );
        }

        return $key;
    }
}
