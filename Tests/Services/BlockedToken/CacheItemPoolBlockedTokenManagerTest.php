<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Services\BlockedToken;

use DateTime;
use DateTimeImmutable;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\MissingClaimException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\BlockedToken\CacheItemPoolBlockedTokenManager;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class CacheItemPoolBlockedTokenManagerTest extends TestCase
{
    private const JTI = '3de41d11099ed70e23e634eb32c959da';
    private const IAT = 1699455323;

    public function testAddPayloadWithoutExpirationShouldThrowsAnException()
    {
        $this->expectException(MissingClaimException::class);
        $cacheAdapter = new ArrayAdapter();
        $blockedTokenManager = new CacheItemPoolBlockedTokenManager($cacheAdapter);
        $blockedTokenManager->add(
            [
                'iat' => self::IAT,
                'jti' => self::JTI,
                'roles' => [
                    'ROLE_USER'
                ],
                'username' => 'lexik'
            ]
        );
    }

    public function testAddPayloadWithoutJitShouldThrowsAnException()
    {
        $this->expectException(MissingClaimException::class);
        $cacheAdapter = new ArrayAdapter();
        $blockedTokenManager = new CacheItemPoolBlockedTokenManager($cacheAdapter);
        $blockedTokenManager->add(
            [
                'iat' => self::IAT,
                "exp" => (int) (new DateTime('2050-01-01'))->format('U'),
                'roles' => [
                    'ROLE_USER'
                ],
                'username' => 'lexik'
            ]
        );
    }

    public function testShouldNotAddPayloadIfItHasExpired()
    {
        $cacheAdapter = new ArrayAdapter();
        $blockedTokenManager = new CacheItemPoolBlockedTokenManager($cacheAdapter);
        self::assertFalse(
            $blockedTokenManager->add(
                [
                    'iat' => self::IAT,
                    'jti' => self::JTI,
                    "exp" => (int) (new DateTime('2020-01-01'))->format('U'),
                    'roles' => [
                        'ROLE_USER'
                    ],
                    'username' => 'lexik'
                ]
            )
        );
        self::assertCount(0, $cacheAdapter->getItems());
    }

    public function testShouldBlockTokenIfPaylaodHasNotExpired()
    {
        ClockMock::register(ArrayAdapter::class);

        $cacheAdapter = new ArrayAdapter();
        $blockedTokenManager = new CacheItemPoolBlockedTokenManager($cacheAdapter);

        $expirationDateTime = new DateTimeImmutable('2050-01-01 00:00:00');
        self::assertTrue(
            $blockedTokenManager->add(
                [
                    'iat' => self::IAT,
                    'jti' => self::JTI,
                    "exp" => (int) $expirationDateTime->format('U'),
                    'roles' => [
                        'ROLE_USER'
                    ],
                    'username' => 'lexik'
                ]
            )
        );
        self::assertCount(1, $cacheAdapter->getValues());

        self::assertTrue($cacheAdapter->hasItem(self::JTI));
        self::assertNotNull($cacheAdapter->getItem(self::JTI));

        ClockMock::withClockMock(($expirationDateTime->modify('+5 minutes 1 second')->format('U')));
        self::assertFalse($cacheAdapter->hasItem(self::JTI), 'The cache item should have expired');
        ClockMock::withClockMock(false);
    }

    public function testHasToken()
    {
        $cacheAdapter = new ArrayAdapter();
        $blockedTokenManager = new CacheItemPoolBlockedTokenManager($cacheAdapter);

        $expirationDateTime = new DateTimeImmutable('2050-01-01 00:00:00');
        $payload = [
            'iat' => self::IAT,
            'jti' => self::JTI,
            "exp" => (int) $expirationDateTime->format('U'),
            'roles' => [
                'ROLE_USER'
            ],
            'username' => 'lexik'
        ];

        self::assertFalse($blockedTokenManager->has($payload));

        $blockedTokenManager->add(
            [
                'iat' => self::IAT,
                'jti' => self::JTI,
                "exp" => (int) $expirationDateTime->format('U'),
                'roles' => [
                    'ROLE_USER'
                ],
                'username' => 'lexik'
            ]
        );
        self::assertTrue($blockedTokenManager->has($payload));

        $blockedTokenManager->remove($payload);
        self::assertFalse($blockedTokenManager->has($payload));
    }
}
