<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * The "AND" in the if statement is a temporary fix for the following issue:
 * https://github.com/lexik/LexikJWTAuthenticationBundle/issues/944
 * https://github.com/vimeo/psalm/issues/7923
 */
if (80000 <= \PHP_VERSION_ID and (new \ReflectionMethod(JsonResponse::class, 'setData'))->hasReturnType()) {
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
                return parent::setData((array)$data + ["code" => $this->statusCode, "message" => $this->getMessage()]);
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
            return parent::setData((array)$data + ['code' => $this->statusCode, 'message' => $this->getMessage()]);
        }
    }
}
