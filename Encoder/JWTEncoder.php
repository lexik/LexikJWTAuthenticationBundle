<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Encoder;

use InvalidArgumentException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\OpenSSLKeyLoader;
use Namshi\JOSE\SimpleJWS;

/**
 * JWTEncoder
 *
 * @author Dev Lexik <dev@lexik.fr>
 */
class JWTEncoder implements JWTEncoderInterface
{
    /**
     * @var string
     */
    private $algorithm;

    /**
     * @var OpenSSLKeyLoader
     */
    protected $keyLoader;

    /**
     * @param OpenSSLKeyLoader $keyLoader
     */
    public function __construct(OpenSSLKeyLoader $keyLoader, $algorithm)
    {
        $this->keyLoader = $keyLoader;
        $this->algorithm = $algorithm;
    }

    /**
     * {@inheritdoc}
     */
    public function encode(array $data)
    {
        $jws = new SimpleJWS(['alg' => $this->algorithm]);
        $jws->setPayload($data);
        $jws->sign($this->keyLoader->loadKey('private'));

        return $jws->getTokenString();
    }

    /**
     * {@inheritdoc}
     */
    public function decode($token)
    {
        try {
            /** @var SimpleJWS $jws */
            $jws = SimpleJWS::load($token);
        } catch (InvalidArgumentException $e) {
            return false;
        }

        if (!$jws->isValid($this->keyLoader->loadKey('public'), $this->algorithm)) {
            return false;
        }

        return $jws->getPayload();
    }
}
