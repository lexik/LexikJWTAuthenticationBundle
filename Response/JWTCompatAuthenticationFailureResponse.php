<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Response;

use ReflectionMethod;
use Symfony\Component\HttpFoundation\JsonResponse;

if ((new ReflectionMethod(JsonResponse::class, 'setData'))->hasReturnType()) {
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
