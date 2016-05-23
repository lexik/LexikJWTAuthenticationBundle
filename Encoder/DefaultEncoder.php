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
class DefaultEncoder implements JWTEncoderInterface
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
     */
    public function encode(array $payload)
    {
        try {
            $jws = $this->jwsProvider->create($payload);
        } catch (InvalidArgumentException $e) {
            throw new JWTEncodeFailureException('An error occurred while trying to encode the JWT token.', $e);
        }

        if (!$jws->isSigned()) {
            throw new UnsignedJWTEncodeFailureException();
        }

        return $jws->getToken();
    }

    /**
     * {@inheritdoc}
     */
    public function decode($token)
    {
        try {
            $jws = $this->jwsProvider->load($token);
        } catch (InvalidArgumentException $e) {
            throw new JWTDecodeFailureException('Invalid JWT Token', $e);
        }

        if ($jws->isExpired()) {
            throw new ExpiredJWTDecodeFailureException();
        }

        if (!$jws->isVerified()) {
            throw new UnverifiedJWTDecodeFailureException();
        }

        return $jws->getPayload();
    }
}
