<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Extractor;

use Symfony\Component\HttpFoundation\Request;

/**
 * AuthorizationHeaderExtractor
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class AuthorizationHeaderExtractor implements RequestTokenExtractorInterface
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
    public function getRequestToken(Request $request)
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
