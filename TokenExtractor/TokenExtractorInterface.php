<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor;

use Symfony\Component\HttpFoundation\Request;

/**
 * TokenExtractorInterface.
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
interface TokenExtractorInterface
{
    /**
     * @return string|false
     */
    public function extract(Request $request);
}
