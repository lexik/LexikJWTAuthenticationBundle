<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Extractor;

use Symfony\Component\HttpFoundation\Request;

/**
 * RequestTokenExtractor
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
interface RequestTokenExtractorInterface
{
    /**
     * @param Request $request
     *
     * @return string
     */
    public function getRequestToken(Request $request);
}
