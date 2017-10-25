<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\TokenExtractor;

use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\QueryParameterTokenExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * QueryParameterTokenExtractorTest.
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class QueryParameterTokenExtractorTest extends TestCase
{
    /**
     * test getRequestToken.
     */
    public function testGetTokenRequest()
    {
        $extractor = new QueryParameterTokenExtractor('bearer');

        $request = new Request(['bear' => 'testtoken']);
        $this->assertFalse($extractor->extract($request));

        $request = new Request(['bearer' => 'testtoken']);
        $this->assertEquals('testtoken', $extractor->extract($request));
    }
}
