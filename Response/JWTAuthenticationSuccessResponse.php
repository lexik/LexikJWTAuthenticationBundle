<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Response sent on successful JWT authentication.
 *
 * @internal
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
final class JWTAuthenticationSuccessResponse extends JsonResponse
{
    /**
     * The Json Web Token.
     *
     * Immutable property.
     *
     * @var string
     */
    private $token;

    /**
     * @param string $token   Json Web Token
     * @param array  $data    Extra data passed to the response body.
     * @param array  $headers HTTP headers
     */
    public function __construct($token, array $extraData = [])
    {
        $this->token     = $token;
        $this->extraData = $extraData;

        parent::__construct();

        $this->setBody();
    }

    /**
     * Gets the Json Web Token.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * {@inheritdoc}
     */
    public function setExtraData(array $extraData = [])
    {
        $this->extraData = $extraData;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtraData()
    {
        return $this->extraData;
    }

    /**
     * Prevents unexpected response content.
     *
     * @internal
     *
     * {@inheritdoc}
     */
    public function setData($data = [])
    {
        return $this->setBody();
    }

    /**
     * Creates the response body.
     *
     * @return JWTAuthenticationSuccessResponse
     */
    private function setBody()
    {
        parent::setData(['token' => $this->token] + $this->extraData);
    }
}
