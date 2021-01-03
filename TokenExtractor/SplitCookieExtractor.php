<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor;

use Symfony\Component\HttpFoundation\Request;

/**
 * SplitCookieExtractor.
 *
 * @author Adam Lukacovic <adam@adamlukacovic.sk>
 */
class SplitCookieExtractor implements TokenExtractorInterface
{
    /**
     * @var array
     */
    private $cookies;

    /**
     * @param array $cookies
     */
    public function __construct($cookies)
    {
        $this->cookies = $cookies;
    }

    /**
     * {@inheritDoc}
     */
    public function extract(Request $request)
    {
        $jwtCookies = [];

        foreach ($this->cookies as $cookie) {
            $jwtCookies[] = $request->cookies->get($cookie, false);
        }

        if (empty(array_filter($jwtCookies))) {
            return false;
        }

        return implode('.', $jwtCookies);
    }
}
