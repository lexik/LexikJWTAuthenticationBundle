<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * JWTAuthenticationFailureResponse.
 *
 * Response sent on failed JWT authentication (can be replaced by a custom Response).
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
final class JWTAuthenticationFailureResponse extends JsonResponse
{
    private $message;

    public function __construct(string $message = 'Bad credentials', int $statusCode = JsonResponse::HTTP_UNAUTHORIZED)
    {
        $this->message = $message;

        parent::__construct(null, $statusCode, ['WWW-Authenticate' => 'Bearer']);
    }

    /**
     * Sets the failure message.
     *
     * @param string $message
     *
     * @return JWTAuthenticationFailureResponse
     */
    public function setMessage($message)
    {
        $this->message = $message;

        $this->setData();

        return $this;
    }

    /**
     * Gets the failure message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Sets the response data with the statusCode & message included.
     *
     * {@inheritdoc}
     */
    public function setData($data = [])
    {
        parent::setData((array) $data + ['code' => $this->statusCode, 'message' => $this->message]);
    }
}
