<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Helper;

/**
 * JWTSplitter.
 *
 * @author Adam Lukacovic <adam@adamlukacovic.sk>
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
     * @param string $jwt
     */
    public function __construct($jwt)
    {
        list($this->header, $this->payload, $this->signature) = explode('.', $jwt);
    }

    /**
     * @param array $parts
     * @return string
     */
    public function getParts($parts = [])
    {
        if (empty($parts)) {
            return implode('.', get_object_vars($this));
        }

        return implode('.', array_intersect_key(get_object_vars($this), array_flip($parts)));
    }
}
