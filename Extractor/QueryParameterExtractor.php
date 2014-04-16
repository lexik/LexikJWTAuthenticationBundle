<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Extractor;

use Symfony\Component\HttpFoundation\Request;

/**
 * QueryParameterExtractor
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class QueryParameterExtractor implements RequestTokenExtractorInterface
{
    /**
     * @var string
     */
    protected $parameterName;

    /**
     * @param string $parameterName
     */
    public function __construct($parameterName)
    {
        $this->parameterName = $parameterName;
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    public function getRequestToken(Request $request)
    {
        return $request->query->get($this->parameterName, false);
    }
}
