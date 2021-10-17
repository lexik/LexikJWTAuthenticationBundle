<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

if (80000 <= \PHP_VERSION_ID && (new \ReflectionMethod(JsonResponse::class, 'setData'))->hasReturnType()) {
    eval('
        namespace Lexik\Bundle\JWTAuthenticationBundle\Response;

        use Symfony\Component\HttpFoundation\JsonResponse;

        /**
         * Compatibility layer for Symfony 6.0 and later.
         *
         * @internal
         */
        abstract class JWTCompatAuthenticationFailureResponse extends JsonResponse
        {
            /**
             * Sets the response data with the statusCode & message included.
             *
             * {@inheritdoc}
             *
             * @return static
             */
            public function setData($data = []): static
            {
                return parent::setData((array) $data + ["code" => $this->statusCode, "message" => $this->getMessage()]);
            }
        }
    ');
} else {
    /**
     * Compatibility layer for Symfony 5.4 and earlier.
     *
     * @internal
     */
    abstract class JWTCompatAuthenticationFailureResponse extends JsonResponse
    {
        /**
         * Sets the response data with the statusCode & message included.
         *
         * {@inheritdoc}
         *
         * @return static
         */
        public function setData($data = [])
        {
            return parent::setData((array) $data + ['code' => $this->statusCode, 'message' => $this->getMessage()]);
        }
    }
}

/**
 * JWTAuthenticationFailureResponse.
 *
 * Response sent on failed JWT authentication (can be replaced by a custom Response).
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
final class JWTAuthenticationFailureResponse extends JWTCompatAuthenticationFailureResponse
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
}
