<?php

namespace Extractor;

use Lexik\Bundle\JWTAuthenticationBundle\Extractor\AuthorizationHeaderExtractor;
use Lexik\Bundle\JWTAuthenticationBundle\Extractor\QueryParameterExtractor;
use Symfony\Component\HttpFoundation\Request;

/**
 * QueryParameterExtractorTest
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class QueryParameterExtractorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test getRequestToken
     */
    public function testGetTokenRequest()
    {
        $extractor = new QueryParameterExtractor('bearer');

        $request = new Request(array('bear' => 'testtoken'));
        $this->assertFalse($extractor->getRequestToken($request));

        $request = new Request(array('bearer' => 'testtoken'));
        $this->assertEquals('testtoken', $extractor->getRequestToken($request));
    }
}
