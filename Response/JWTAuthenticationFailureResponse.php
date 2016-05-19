<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * JWTAuthenticationFailureResponse.
 *
 * Response sent on failed JWT authentication (can be replaced by a custom Response).
 *
 * @internal
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
final class JWTAuthenticationFailureResponse extends JsonResponse
{
    /**
     * The response message.
     *
     * @var string
     */
    private $message;

    /**
     * @param string $message A failure message passed in the response body
     */
    public function __construct($message = 'Bad credentials')
    {
        $this->message = $message;

        parent::__construct(null, self::HTTP_UNAUTHORIZED, ['WWW-Authenticate' => 'Bearer']);

        $this->setData([
            'code'    => $this->statusCode,
            'message' => $this->message,
        ]);
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
}
