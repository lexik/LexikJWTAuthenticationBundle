<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader;

/**
 * Load crypto keys for the OpenSSL crypto engine.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class OpenSSLKeyLoader extends AbstractKeyLoader
{
    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException If the key cannot be read
     * @throws \RuntimeException Either the key or the passphrase is not valid
     */
    public function loadKey($type)
    {
        $path         = $this->getKeyPath($type);
        $encryptedKey = file_get_contents($path);
        $key          = call_user_func_array(
            sprintf('openssl_pkey_get_%s', $type),
            self::TYPE_PRIVATE == $type ? [$encryptedKey, $this->getPassphrase()] : [$encryptedKey]
        );

        if (!$key) {
            throw new \RuntimeException(
                sprintf('Failed to load %1$s key "%2$s". Did you correctly set the "lexik_jwt_authentication.jwt_%1$s_key_path" config option?', $type, $path)
            );
        }

        return $key;
    }
}
