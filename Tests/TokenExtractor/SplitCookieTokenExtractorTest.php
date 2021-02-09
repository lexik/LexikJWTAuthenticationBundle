<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\TokenExtractor;

use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\SplitCookieExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * SplitCookieTokenExtractorTest.
 *
 * @author Adam Lukacovic <adam@adamlukacovic.sk>
 */
class SplitCookieTokenExtractorTest extends TestCase
{
    public function testGetTokenRequest()
    {
        $extractor = new SplitCookieExtractor(['jwt_hp', 'jwt_s']);

        $request = new Request();
        $this->assertFalse($extractor->extract($request));

        $request = new Request();
        $request->cookies->add(['jwt_hp' => 'testheader.testpayload']);
        $request->cookies->add(['jwt_s' => 'testsignature']);
        $this->assertEquals('testheader.testpayload.testsignature', $extractor->extract($request));
    }
}
