<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor;

use Symfony\Component\HttpFoundation\Request;

/**
 * CookieTokenExtractor
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class CookieTokenExtractor implements TokenExtractorInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @param Request $request
     * @return string
     */
    public function extract(Request $request)
    {
        if (!$request->cookies->has($this->name)) {
            return false;
        }

        return $request->cookies->get($this->name);
    }
}
