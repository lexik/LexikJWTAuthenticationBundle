<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider;

@trigger_error(sprintf('The "%s" class is deprecated since version 2.5 and will be removed in 3.0. Use "%s" or create your own "%s" implementation instead.', DefaultJWSProvider::class, LcobucciJWSProvider::class, JWSProviderInterface::class), E_USER_DEPRECATED);

use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\KeyLoaderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\CreatedJWS;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\LoadedJWS;
use Namshi\JOSE\JWS;

/**
 * JWS Provider, Namshi\JOSE library integration.
 * Supports OpenSSL and phpseclib crypto engines.
 *
 * @final
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 *
 * @deprecated since version 2.5, to be removed in 3.0
 */
class DefaultJWSProvider implements JWSProviderInterface
{
    /**
     * @var KeyLoaderInterface
     */
    private $keyLoader;

    /**
     * @var string
     */
    private $cryptoEngine;

    /**
     * @var string
     */
    private $signatureAlgorithm;

    /**
     * @var int
     */
    private $ttl;

    /**
     * @var int
     */
    private $clockSkew;

    /**
     * @param KeyLoaderInterface $keyLoader
     * @param string             $cryptoEngine
     * @param string             $signatureAlgorithm
     * @param int                $ttl
     * @param int                $clockSkew
     *
     * @throws \InvalidArgumentException If the given algorithm is not supported
     */
    public function __construct(KeyLoaderInterface $keyLoader, $cryptoEngine, $signatureAlgorithm, $ttl, $clockSkew)
    {
        if (null !== $ttl && !is_numeric($ttl)) {
            throw new \InvalidArgumentException(sprintf('The TTL should be a numeric value, got %s instead.', $ttl));
        }

        if (null !== $clockSkew && !is_numeric($clockSkew)) {
            throw new \InvalidArgumentException(sprintf('The clock skew should be a numeric value, got %s instead.', $clockSkew));
        }

        $cryptoEngine = 'openssl' == $cryptoEngine ? 'OpenSSL' : 'SecLib';

        if (!$this->isAlgorithmSupportedForEngine($cryptoEngine, $signatureAlgorithm)) {
            throw new \InvalidArgumentException(
                sprintf('The algorithm "%s" is not supported for %s', $signatureAlgorithm, $cryptoEngine)
            );
        }

        $this->keyLoader          = $keyLoader;
        $this->cryptoEngine       = $cryptoEngine;
        $this->signatureAlgorithm = $signatureAlgorithm;
        $this->ttl                = $ttl;
        $this->clockSkew          = $clockSkew;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $payload, array $header = [])
    {
        $header['alg'] = $this->signatureAlgorithm;
        $jws           = new JWS($header, $this->cryptoEngine);
        $claims        = ['iat' => time()];

        if (null !== $this->ttl && !isset($payload['exp'])) {
            $claims['exp'] = time() + $this->ttl;
        }

        $jws->setPayload($payload + $claims);
        $jws->sign(
            $this->keyLoader->loadKey('private'),
            $this->keyLoader->getPassphrase()
        );

        return new CreatedJWS($jws->getTokenString(), $jws->isSigned());
    }

    /**
     * {@inheritdoc}
     */
    public function load($token)
    {
        $jws = JWS::load($token, false, null, $this->cryptoEngine);

        return new LoadedJWS(
            $jws->getPayload(),
            $jws->verify($this->keyLoader->loadKey('public'), $this->signatureAlgorithm),
            null !== $this->ttl,
            $jws->getHeader(),
            $this->clockSkew
        );
    }

    /**
     * @param string $cryptoEngine
     * @param string $signatureAlgorithm
     *
     * @return bool
     */
    private function isAlgorithmSupportedForEngine($cryptoEngine, $signatureAlgorithm)
    {
        $signerClass = sprintf('Namshi\\JOSE\\Signer\\%s\\%s', $cryptoEngine, $signatureAlgorithm);

        return class_exists($signerClass);
    }
}
