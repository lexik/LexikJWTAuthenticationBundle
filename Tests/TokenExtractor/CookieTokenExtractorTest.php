<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\TokenExtractor;

use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\CookieTokenExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * CookieTokenExtractorTest.
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class CookieTokenExtractorTest extends TestCase
{
    /**
     * test getRequestToken.
     */
    public function testGetTokenRequest()
    {
        $extractor = new CookieTokenExtractor('BEARER');

        $request = new Request();
        $this->assertFalse($extractor->extract($request));

        $request = new Request();
        $request->cookies->add(['BEAR' => 'testheader.testpayload.testsignature']);
        $this->assertFalse($extractor->extract($request));

        $request = new Request();
        $request->cookies->add(['BEARER' => 'testheader.testpayload.testsignature']);
        $this->assertEquals('testheader.testpayload.testsignature', $extractor->extract($request));
    }
}
