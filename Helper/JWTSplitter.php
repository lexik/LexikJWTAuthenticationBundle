<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Helper;

/**
 * JWTSplitter.
 *
 * @author Adam Lukacovic <adam@adamlukacovic.sk>
 *
 * @final
 */
class JWTSplitter
{
    private string $header;

    private string $payload;

    private string $signature;

    /**
     * @var string
     */
    private $jwt;

    public function __construct(string $jwt)
    {
        $this->jwt = $jwt;
        [$this->header, $this->payload, $this->signature] = explode('.', $jwt);
    }

    public function getParts(array $parts = []): string
    {
        if (!$parts) {
            return $this->jwt;
        }

        return implode('.', array_intersect_key(get_object_vars($this), array_flip($parts)));
    }
}
