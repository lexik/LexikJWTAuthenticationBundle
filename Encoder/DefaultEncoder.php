<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Encoder;

use InvalidArgumentException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProviderInterface;

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
            throw new JWTEncodeFailureException('Unable to create a signed JWT from the given configuration.');
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
            throw new JWTDecodeFailureException('Expired JWT Token');
        }

        if (!$jws->isVerified()) {
            throw new JWTDecodeFailureException('Unable to verify the given JWT through the given configuration. If the "lexik_jwt_authentication.encoder" encryption options have been changed since your last authentication, please renew the token. If the problem persists, verify that the configured keys/passphrase are valid.');
        }

        return $jws->getPayload();
    }
}
