<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader;

/**
 * Load crypto keys for the OpenSSL crypto engine.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class OpenSSLKeyLoader extends AbstractKeyLoader implements KeyDumperInterface
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
            $sslError = '';
            while ($msg = trim(openssl_error_string(), " \n\r\t\0\x0B\"")) {
                if (substr($msg, 0, 6) === 'error:') {
                    $msg = substr($msg, 6);
                }
                $sslError .= "\n ".$msg;
            }
            throw new \RuntimeException(
                sprintf('Failed to load %s key "%s": %s', $type, $path, $sslError)
            );
        }

        return $key;
    }

    /**
     * {@inheritdoc}
     */
    public function dumpKey()
    {
        $key = openssl_pkey_get_details($this->loadKey('public'));

        if (!isset($key['key'])) {
            return;
        }

        return $key['key'];
    }
}
