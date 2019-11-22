<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\TokenExtractor;

use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\ChainTokenExtractor;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * ChainTokenExtractorTest.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class ChainTokenExtractorTest extends TestCase
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

    public function testRemoveExtractor()
    {
        $extractor = new ChainTokenExtractor([]);
        $custom    = $this->getTokenExtractorMock(null);

        $extractor->addExtractor($custom);
        $result = $extractor->removeExtractor(function (TokenExtractorInterface $extractor) use ($custom) {
            return $extractor === $custom;
        });

        $this->assertTrue($result, 'removeExtractor returns true in case of success, false otherwise');
        $this->assertFalse($extractor->getIterator()->valid(), 'The token extractor should have been removed so the map should be empty');
    }

    public function testExtract()
    {
        $this->assertEquals('dummy', (new ChainTokenExtractor($this->getTokenExtractorMap([false, false, 'dummy'])))->extract(new Request()));
    }

    public function testClearMap()
    {
        $extractor = new ChainTokenExtractor($this->getTokenExtractorMap());
        $extractor->clearMap();

        $this->assertFalse($extractor->getIterator()->valid());
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
}
