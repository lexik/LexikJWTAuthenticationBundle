<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\TokenExtractor;

use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * AuthorizationHeaderTokenExtractorTest.
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class AuthorizationHeaderTokenExtractorTest extends TestCase
{
    /**
     * test getRequestToken.
     */
    public function testGetTokenRequest()
    {
        $extractor = new AuthorizationHeaderTokenExtractor('Bearer', 'Authorization');

        $request = new Request();
        $this->assertFalse($extractor->extract($request));

        $request = new Request();
        $request->headers->set('Authorization', 'Bear testtoken');
        $this->assertFalse($extractor->extract($request));

        $request = new Request();
        $request->headers->set('Authorizat', 'Bearer testtoken');
        $this->assertFalse($extractor->extract($request));

        $request = new Request();
        $request->headers->set('Authorization', 'Bearer testtoken');
        $this->assertEquals('testtoken', $extractor->extract($request));
    }

    public function testTokenTypeIsCaseInsensitive()
    {
        $extractor = new AuthorizationHeaderTokenExtractor('MyToKeNTypE', 'Authorization');

        $request = new Request();
        $request->headers->set('Authorization', 'MyTokenType testtoken');
        $this->assertEquals('testtoken', $extractor->extract($request));
    }
}
