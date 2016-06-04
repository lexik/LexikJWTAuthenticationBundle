<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\TokenExtractor;

use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\QueryParameterTokenExtractor;
use Symfony\Component\HttpFoundation\Request;

/**
 * QueryParameterTokenExtractorTest.
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class QueryParameterTokenExtractorTest extends \PHPUnit_Framework_TestCase
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
