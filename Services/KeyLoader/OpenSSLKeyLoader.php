<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader;

@trigger_error(sprintf('The "%s\OpenSSLKeyLoader" class is deprecated since version 2.5 and will be removed in 3.0. Use "%s" instead.', __NAMESPACE__, RawKeyLoader::class), E_USER_DEPRECATED);

/**
 * Load crypto keys for the OpenSSL crypto engine.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 *
 * @deprecated since version 2.5, to be removed in 3.0. Use RawKeyLoader instead
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
        if (!in_array($type, [self::TYPE_PUBLIC, self::TYPE_PRIVATE])) {
            throw new \InvalidArgumentException(sprintf('The key type must be "public" or "private", "%s" given.', $type));
        }

        $rawKey = file_get_contents($this->getKeyPath($type));
        $key = call_user_func_array("openssl_pkey_get_$type", self::TYPE_PRIVATE == $type ? [$rawKey, $this->getPassphrase()] : [$rawKey]);

        if (!$key) {
            $sslError = '';
            while ($msg = trim(openssl_error_string(), " \n\r\t\0\x0B\"")) {
                if ('error:' === substr($msg, 0, 6)) {
                    $msg = substr($msg, 6);
                }
                $sslError .= "\n $msg";
            }

            throw new \RuntimeException(sprintf('Failed to load %s key: %s', $type, $sslError));
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
