<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor;

use Lexik\Bundle\JWTAuthenticationBundle\Services\TokenExtractorMap;
use Symfony\Component\HttpFoundation\Request;

/**
 * ChainTokenExtractor is the class responsible of extracting a JWT token
 * from a {@link Request} object using all mapped token extractors.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class ChainTokenExtractor implements TokenExtractorInterface
{
    /**
     * @var TokenExtractorMap
     */
    private $map;

    /**
     * @param TokenExtractorMap $map
     */
    public function __construct(TokenExtractorMap $map)
    {
        $this->map = $map;
    }

    /**
     * Iterates over the token extractors map calling {@see extract()}
     * until a token is found.
     *
     * {@inheritdoc}
     */
    public function extract(Request $request)
    {
        foreach ($this->map->loadExtractors() as $extractor) {
            if ($token = $extractor->extract($request)) {
                return $token;
            }
        }

        return false;
    }

    /**
     * @return TokenExtractorMap
     */
    public function getMap()
    {
        return $this->map;
    }
}
