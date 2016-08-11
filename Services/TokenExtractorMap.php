<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services;

use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * TokenExtractorMap is a map of services implementing {@link TokenExtractorInterface}
 * that can be loaded on demand.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class TokenExtractorMap implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var array
     */
    private $map = [];

    /**
     * @param array $map An array of service ids implementing {@link TokenExtractorInterface}
     */
    public function __construct(array $map = [])
    {
        $this->map = $map;
    }

    /**
     * Adds a new token extractor to the map.
     *
     * @param string $id The service id
     */
    public function add($id)
    {
        if (in_array($id, $this->map)) {
            return;
        }

        if (false === $this->container->has($id)) {
            throw new \InvalidArgumentException(sprintf('The first argument of "%s()" must be a valid service identifier refering to a "%s" implementation, "%s" given.', __METHOD__, TokenExtractorInterface::class, $id));
        }

        $this->map[] = $id;
    }

    /**
     * Removes a token extractor from the map.
     *
     * Note: This easily disables a token extractor, the service will be
     * removed from the map of the current instance but not definitely, the
     * map will be entirely resetted at the next instance. For overriding
     * the default map, see the "@lexik_jwt_authentication.token_extractor_map"
     * service.
     *
     * @param string $id The token extractor service id
     */
    public function remove($id)
    {
        if ($key = array_search($id, $this->map)) {
            unset($this->map[$key]);
        }
    }

    /**
     * Loads the mapped token extractors.
     *
     * An extractor is initialized only if we really need it (at
     * the corresponding iteration).
     *
     * @return \Generator The generated TokenExtractorInterface implementations
     */
    public function loadExtractors()
    {
        foreach ($this->map as $id) {
            $extractor = $this->container->get($id);

            if ($extractor instanceof TokenExtractorInterface) {
                yield $extractor;
            }
        }
    }
}
