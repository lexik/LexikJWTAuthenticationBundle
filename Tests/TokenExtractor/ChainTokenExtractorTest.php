<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\TokenExtractor;

use Lexik\Bundle\JWTAuthenticationBundle\Services\TokenExtractorMap;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\ChainTokenExtractor;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * AuthorizationHeaderTokenExtractorTest.
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class ChainTokenExtractorTest extends \PHPUnit_Framework_TestCase
{
    public function testExtract()
    {
        $request = new Request();

        $tokenExtractorMap = $this
            ->getMockBuilder(TokenExtractorMap::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tokenExtractorMap
            ->expects($this->once())
            ->method('loadExtractors')
            ->willReturn($this->generateTokenExtractorMocks([false, false, 'dummy']));

        $chainExtractor = new ChainTokenExtractor($tokenExtractorMap);

        $this->assertEquals('dummy', $chainExtractor->extract($request));
    }

    public function testGetMap()
    {
        $tokenExtractorMap = $this
            ->getMockBuilder(TokenExtractorMap::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertSame($tokenExtractorMap, (new ChainTokenExtractor($tokenExtractorMap))->getMap());
    }

    private function getTokenExtractorMock($returnValue)
    {
        $extractor = $this
            ->getMockBuilder(TokenExtractorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $extractor
            ->expects($this->once())
            ->method('extract')
            ->with($this->isInstanceOf(Request::class))
            ->willReturn($returnValue);

        return $extractor;
    }

    private function generateTokenExtractorMocks($returnValues)
    {
        foreach ($returnValues as $value) {
            yield $this->getTokenExtractorMock($value);
        }
    }
}
