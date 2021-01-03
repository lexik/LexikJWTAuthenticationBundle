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
    /**
     * @var string
     */
    private $header;

    /**
     * @var string
     */
    private $payload;

    /**
     * @var string
     */
    private $signature;

    public function __construct(string $jwt)
    {
        [$this->header, $this->payload, $this->signature] = explode('.', $jwt);
    }

    /**
     * @param array $parts
     *
     * @return string
     */
    public function getParts($parts = [])
    {
        if (!$parts) {
            return "$this->header.$this->payload.$this->signature";
        }

        return implode('.', array_intersect_key(get_object_vars($this), array_flip($parts)));
    }
}
