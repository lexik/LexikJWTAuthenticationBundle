<?php

namespace Extractor;

use Lexik\Bundle\JWTAuthenticationBundle\Extractor\AuthorizationHeaderExtractor;
use Symfony\Component\HttpFoundation\Request;

/**
 * AuthorizationHeaderExtractorTest
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class AuthorizationHeaderExtractorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test getRequestToken
     */
    public function testGetTokenRequest()
    {
        $extractor = new AuthorizationHeaderExtractor('Bearer');

        $request = new Request();
        $request->headers->set('Authorization', 'Bear testtoken');
        $this->assertFalse($extractor->getRequestToken($request));

        $request = new Request();
        $request->headers->set('Authorization', 'Bearer testtoken');
        $this->assertEquals('testtoken', $extractor->getRequestToken($request));
    }
}
