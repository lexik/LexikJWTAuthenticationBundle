<?php

namespace Services;

use Lexik\Bundle\JWTAuthenticationBundle\Services\TokenExtractorMap;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * TokenExtractorMap Test.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class TokenExtractorMapTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadExtractors()
    {
        $extractors = [$this->getTokenExtractorMock(), $this->getTokenExtractorMock()];
        $container  = $this->getContainerMock();
        $container
            ->expects($this->at(0))
            ->method('get')
            ->with('foo_extractor')
            ->willReturn($extractors[0]);
        $container
            ->expects($this->at(1))
            ->method('get')
            ->with('bar_extractor')
            ->willReturn($extractors[1]);

        $map = new TokenExtractorMap(['foo_extractor', 'bar_extractor']);
        $map->setContainer($container);

        $loadedExtractors = [];
        foreach ($map->loadExtractors() as $extractor) {
            $loadedExtractors[] = $extractor;
        }

        $this->assertSame($extractors, $loadedExtractors);
    }

    public function testAdd()
    {
        $container = $this->getContainerMock();
        $container
            ->expects($this->once())
            ->method('has')
            ->with('extra')
            ->willReturn(true);

        $map = new TokenExtractorMap(['first']);
        $map->setContainer($container);
        $map->add('extra');

        $this->assertAttributeSame(['first', 'extra'], 'map', $map);
    }

    public function testRemove()
    {
        $map = new TokenExtractorMap(['first', 'second']);
        $map->remove('second');

        $this->assertAttributeSame(['first'], 'map', $map);
    }

    private function getContainerMock()
    {
        return $this
            ->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getTokenExtractorMock()
    {
        return $this
            ->getMockBuilder(TokenExtractorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
