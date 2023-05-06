<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Encoder;

use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;

/**
 * Json Web Token encoder/decoder based on the lcobucci/jwt library.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class LcobucciJWTEncoder implements JWTEncoderInterface, HeaderAwareJWTEncoderInterface
{
    protected JWSProviderInterface $jwsProvider;

    public function __construct(JWSProviderInterface $jwsProvider)
    {
        $this->jwsProvider = $jwsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function encode(array $payload, array $header = [])
    {
        try {
            $jws = $this->jwsProvider->create($payload, $header);
        } catch (\InvalidArgumentException $e) {
            throw new JWTEncodeFailureException(JWTEncodeFailureException::INVALID_CONFIG, 'An error occurred while trying to encode the JWT token. Please verify your configuration (private key/passphrase)', $e, $payload);
        }

        if (!$jws->isSigned()) {
            throw new JWTEncodeFailureException(JWTEncodeFailureException::UNSIGNED_TOKEN, 'Unable to create a signed JWT from the given configuration.', null, $payload);
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
        } catch (\Exception $e) {
            throw new JWTDecodeFailureException(JWTDecodeFailureException::INVALID_TOKEN, 'Invalid JWT Token', $e);
        }

        if ($jws->isInvalid()) {
            throw new JWTDecodeFailureException(JWTDecodeFailureException::INVALID_TOKEN, 'Invalid JWT Token', null, $jws->getPayload());
        }

        if ($jws->isExpired()) {
            throw new JWTDecodeFailureException(JWTDecodeFailureException::EXPIRED_TOKEN, 'Expired JWT Token', null, $jws->getPayload());
        }

        if (!$jws->isVerified()) {
            throw new JWTDecodeFailureException(JWTDecodeFailureException::UNVERIFIED_TOKEN, 'Unable to verify the given JWT through the given configuration. If the "lexik_jwt_authentication.encoder" encryption options have been changed since your last authentication, please renew the token. If the problem persists, verify that the configured keys/passphrase are valid.', null, $jws->getPayload());
        }

        return $jws->getPayload();
    }
}
