<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor;

use Symfony\Component\HttpFoundation\Request;

/**
 * AuthorizationHeaderTokenExtractor
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class AuthorizationHeaderTokenExtractor implements TokenExtractorInterface
{
    /**
     * @var string
     */
    protected $prefix;

    /**
     * @param string $prefix
     */
    public function __construct($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    public function extract(Request $request)
    {
        if (!$request->headers->has('Authorization')) {
            return false;
        }

        $headerParts = explode(' ', $request->headers->get('Authorization'));

        if (!(count($headerParts) === 2 && $headerParts[0] === $this->prefix)) {
            return false;
        }

        return $headerParts[1];
    }
}
