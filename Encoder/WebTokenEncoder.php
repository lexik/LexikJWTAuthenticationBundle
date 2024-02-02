<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Encoder;

use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\WebToken\AccessTokenBuilder;
use Lexik\Bundle\JWTAuthenticationBundle\Services\WebToken\AccessTokenLoader;

/**
 * Json Web Token encoder/decoder based on the web-token framework.
 *
 * @author Florent Morsellis <florent.morselli@spomky-labs.com>
 */
final class WebTokenEncoder implements HeaderAwareJWTEncoderInterface
{
    /**
     * @var AccessTokenBuilder|null
     */
    private $accessTokenBuilder;

    /**
     * @var AccessTokenLoader|null
     */
    private $accessTokenLoader;

    public function __construct(?AccessTokenBuilder $accessTokenBuilder, ?AccessTokenLoader $accessTokenLoader)
    {
        $this->accessTokenBuilder = $accessTokenBuilder;
        $this->accessTokenLoader = $accessTokenLoader;
    }

    /**
     * {@inheritdoc}
     */
    public function encode(array $payload, array $header = [])
    {
        if (!$this->accessTokenBuilder) {
            throw new \LogicException('The access token issuance features are not enabled.');
        }

        try {
            return $this->accessTokenBuilder->build($header, $payload);
        } catch (\InvalidArgumentException $e) {
            throw new JWTEncodeFailureException(JWTEncodeFailureException::INVALID_CONFIG, 'An error occurred while trying to encode the JWT token. Please verify your configuration (private key/passphrase)', $e, $payload);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function decode($token)
    {
        if (!$this->accessTokenLoader) {
            throw new \LogicException('The access token verification features are not enabled.');
        }

        try {
            return $this->accessTokenLoader->load($token);
        } catch (JWTFailureException $e) {
            throw $e;
        }
    }
}
