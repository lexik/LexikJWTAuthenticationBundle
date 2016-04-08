<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Encoder;

use InvalidArgumentException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailure\ExpiredJWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailure\JWTEncodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailure\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailure\UnverifiedJWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailure\UnsignedJWTEncodeFailureException;

/**
 * Default Json Web Token encoder/decoder.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class DefaultEncoder
{
    /**
     * @var JWSProviderInterface
     */
    protected $jwsProvider;

    /**
     * @param JWSProviderInterface $jwsProvider
     */
    public function __construct(JWSProviderInterface $jwsProvider)
    {
        $this->jwsProvider = $jwsProvider;
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnsignedJWTEncodeFailureException
     */
    public function encode(array $data)
    {
        try {
            $this->jwsProvider->createSignedToken($data);
        } catch (InvalidArgumentException $e) {
            throw new JWTEncodeFailureException('An error occurred while trying to encode the JWT token.', $e);
        }

        if (JWSProviderInterface::SIGNED !== $this->jwsProvider->getStatus()) {
            throw new UnsignedJWTEncodeFailureException();
        }

        return $this->jwsProvider->getToken();
    }

    /**
     * {@inheritdoc}
     *
     * @throws JWTDecodeFailureException           If the signature cannot be loaded
     * @throws UnverifiedJWTDecodeFailureException If the signature cannot be verified
     * @throws ExpiredJWTDecodeFailureException    If the token is expired
     */
    public function decode($token)
    {
        try {
            $this->jwsProvider->loadSignature($token);
        } catch (InvalidArgumentException $e) {
            throw new JWTDecodeFailureException('Invalid JWT Token', $e);
        }

        if (JWSProviderInterface::VERIFIED !== $this->jwsProvider->getStatus()) {
            throw new UnverifiedJWTDecodeFailureException();
        }

        if ($this->jwsProvider->isExpired()) {
            throw new ExpiredJWTDecodeFailureException();
        }

        return $this->jwsProvider->getPayload();
    }
}
