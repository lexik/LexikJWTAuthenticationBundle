<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor;

use Symfony\Component\HttpFoundation\Request;

/**
 * ChainTokenExtractor is the class responsible of extracting a JWT token
 * from a {@link Request} object using all mapped token extractors.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class ChainTokenExtractor implements \IteratorAggregate, TokenExtractorInterface
{
    /**
     * @var array
     */
    private $map;

    /**
     * @param array $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    /**
     * Adds a new token extractor to the map.
     *
     * @param TokenExtractorInterface $extractor
     */
    public function addExtractor(TokenExtractorInterface $extractor)
    {
        $this->map[] = $extractor;
    }

    /**
     * Iterates over the token extractors map calling {@see extract()}
     * until a token is found.
     *
     * {@inheritdoc}
     */
    public function extract(Request $request)
    {
        foreach ($this->getIterator() as $extractor) {
            if ($token = $extractor->extract($request)) {
                return $token;
            }
        }

        return false;
    }

    /**
     * Iterates over the mapped token extractors while generating them.
     *
     * An extractor is initialized only if we really need it (at
     * the corresponding iteration).
     *
     * @return \Generator The generated TokenExtractorInterface implementations
     */
    public function getIterator()
    {
        foreach ($this->map as $extractor) {
            if ($extractor instanceof TokenExtractorInterface) {
                yield $extractor;
            }
        }
    }

    /**
     * Clears the token extractor map.
     */
    public function clearMap()
    {
        $this->map = [];
    }
}
