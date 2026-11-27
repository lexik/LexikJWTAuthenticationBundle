<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services\BlockedToken;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\MissingClaimException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\BlockedTokenManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\Clock;

class CacheItemPoolBlockedTokenManager implements BlockedTokenManagerInterface
{
    private $cacheJwt;

    private ClockInterface $clock;

    public function __construct(CacheItemPoolInterface $cacheJwt, ?ClockInterface $clock = null)
    {
        if (null === $clock) {
            $clock = Clock::get();
        }

        $this->cacheJwt = $cacheJwt;
        $this->clock = $clock;
    }

    public function add(array $payload): bool
    {
        if (!isset($payload['exp'])) {
            throw new MissingClaimException('exp');
        }

        $expiration = new DateTimeImmutable('@' . $payload['exp'], new DateTimeZone('UTC'));
        $now = $this->clock->now();

        // If the token is already expired, there's no point in adding it to storage
        if ($expiration <= $now) {
            return false;
        }

        $cacheExpiration = $expiration->add(new DateInterval('PT5M'));

        if (!isset($payload['jti'])) {
            throw new MissingClaimException('jti');
        }

        $cacheItem = $this->cacheJwt->getItem($payload['jti']);
        $cacheItem->set([]);
        $cacheItem->expiresAt($cacheExpiration);
        $this->cacheJwt->save($cacheItem);

        return true;
    }

    public function has(array $payload): bool
    {
        if (!isset($payload['jti'])) {
            throw new MissingClaimException('jti');
        }

        return $this->cacheJwt->hasItem($payload['jti']);
    }

    public function remove(array $payload): void
    {
        if (!isset($payload['jti'])) {
            throw new MissingClaimException('jti');
        }

        $this->cacheJwt->deleteItem($payload['jti']);
    }
}
