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
    private array $cookies;

    public function __construct(array $cookies)
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

        if (count($this->cookies) !== count(array_filter($jwtCookies))) {
            return false;
        }

        return implode('.', $jwtCookies);
    }
}
