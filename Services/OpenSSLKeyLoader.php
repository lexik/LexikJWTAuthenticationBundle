<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services;

/**
 * Load OpenSSL config keys.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class OpenSSLKeyLoader
{
    /**
     * @var string
     */
    protected $privateKey;

    /**
     * @var string
     */
    protected $publicKey;

    /**
     * @var string
     */
    protected $passphrase;

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
     * Checks that configured keys exists and private key can be parsed using the passphrase
     */
    public function checkOpenSSLConfig()
    {
        $this->loadKey('public');
        $this->loadKey('private');
    }

    /**
     * Load a key from a given type (public or private).
     *
     * @param string Either
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function loadKey($type)
    {
        $property = $type . 'Key';
        $path = $this->$property;

        if (!file_exists($path) || !is_readable($path)) {
            throw new \RuntimeException(sprintf(
                '%s key "%s" does not exist or is not readable. Did you correctly set the "lexik_jwt_authentication.%s_key_path" parameter?',
                ucfirst($type),
                $path,
                $type
            ));
        }

        $encryptedKey = file_get_contents($path);
        $key = call_user_func_array(
            sprintf('openssl_pkey_get_%s', $type),
            $type == 'private' ? [$encryptedKey, $this->passphrase] : [$encryptedKey]
        );

        if (!$key) {
            throw new \RuntimeException(sprintf(
                'Failed to load %s key "%s". Did you correctly configure the corresponding passphrase?',
                $type,
                $path
            ));
        }

        return $key;
    }
}
