<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor;

use Symfony\Component\HttpFoundation\Request;

/**
 * QueryParameterTokenExtractor.
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class QueryParameterTokenExtractor implements TokenExtractorInterface
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
     * {@inheritdoc}
     */
    public function extract(Request $request)
    {
        return $request->query->get($this->parameterName, false);
    }
}
