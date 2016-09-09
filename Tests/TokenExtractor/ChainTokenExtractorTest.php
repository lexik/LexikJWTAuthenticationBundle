<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\TokenExtractor;

use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\ChainTokenExtractor;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * ChainTokenExtractorTest.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class ChainTokenExtractorTest extends \PHPUnit_Framework_TestCase
{
    public function testGetIterator()
    {
        $map = $this->getTokenExtractorMap();

        foreach ((new ChainTokenExtractor($map)) as $extractor) {
            $this->assertContains($extractor, $map);
        }
    }

    public function testAddExtractor()
    {
        $extractor = new ChainTokenExtractor($this->getTokenExtractorMap());
        $custom    = $this->getTokenExtractorMock(null);
        $extractor->addExtractor($custom);

        $map = [];
        foreach ($extractor as $child) {
            $map[] = $child;
        }

        $this->assertCount(4, $map);
        $this->assertContains($custom, $map);
    }

    public function testExtract()
    {
        $this->assertEquals('dummy', (new ChainTokenExtractor($this->getTokenExtractorMap([false, false, 'dummy'])))->extract(new Request()));
    }

    public function testClearMap()
    {
        $extractor = new ChainTokenExtractor($this->getTokenExtractorMap());
        $extractor->clearMap();

        $this->assertNull($extractor->getIterator()->current());
    }

    private function getTokenExtractorMock($returnValue)
    {
        $extractor = $this
            ->getMockBuilder(TokenExtractorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        if ($returnValue) {
            $extractor
                ->expects($this->once())
                ->method('extract')
                ->with($this->isInstanceOf(Request::class))
                ->willReturn($returnValue);
        }

        return $extractor;
    }

    private function getTokenExtractorMap($returnValues = [null, null, null])
    {
        $map = [];

        foreach ($returnValues as $value) {
            $map[] = $this->getTokenExtractorMock($value);
        }

        return $map;
    }

    private function generateTokenExtractors(array $map)
    {
        foreach ($map as $extractor) {
            yield $extractor;
        }
    }
}
