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

    /**
     * @var string
     */
    private $jwt;

    public function __construct(string $jwt)
    {
        $this->jwt = $jwt;
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
            return $this->jwt;
        }

        return implode('.', array_intersect_key(get_object_vars($this), array_flip($parts)));
    }
}
