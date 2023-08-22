<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Cookie;

use Lexik\Bundle\JWTAuthenticationBundle\Helper\JWTSplitter;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Creates secure JWT cookies.
 */
final class JWTCookieProvider
{
    private ?string $defaultName;
    private ?int $defaultLifetime;
    private ?string $defaultSameSite;
    private ?string $defaultPath;
    private ?string $defaultDomain;
    private bool $defaultSecure;
    private bool $defaultHttpOnly;
    private array $defaultSplit;

    public function __construct(?string $defaultName = null, ?int $defaultLifetime = 0, ?string $defaultSameSite = Cookie::SAMESITE_LAX, ?string $defaultPath = '/', ?string $defaultDomain = null, bool $defaultSecure = true, bool $defaultHttpOnly = true, array $defaultSplit = [])
    {
        $this->defaultName = $defaultName;
        $this->defaultLifetime = $defaultLifetime;
        $this->defaultSameSite = $defaultSameSite;
        $this->defaultPath = $defaultPath;
        $this->defaultDomain = $defaultDomain;
        $this->defaultSecure = $defaultSecure;
        $this->defaultHttpOnly = $defaultHttpOnly;
        $this->defaultSplit = $defaultSplit;
    }

    /**
     * Creates a secure cookie containing the passed JWT.
     *
     * For each argument (all args except $jwt), if omitted or set to null then the
     * default value defined via the constructor will be used.
     */
    public function createCookie(string $jwt, ?string $name = null, $expiresAt = null, ?string $sameSite = null, ?string $path = null, ?string $domain = null, ?bool $secure = null, ?bool $httpOnly = null, array $split = []): Cookie
    {
        if (!$name && !$this->defaultName) {
            throw new \LogicException(sprintf('The cookie name must be provided, either pass it as 2nd argument of %s or set a default name via the constructor.', __METHOD__));
        }

        if (!$expiresAt && null === $this->defaultLifetime) {
            throw new \LogicException(sprintf('The cookie expiration time must be provided, either pass it as 3rd argument of %s or set a default lifetime via the constructor.', __METHOD__));
        }

        $jwtParts = new JWTSplitter($jwt);
        $jwt = $jwtParts->getParts($split ?: $this->defaultSplit);

        if (null === $expiresAt) {
            $expiresAt = 0 === $this->defaultLifetime ? 0 : (time() + $this->defaultLifetime);
        }

        return Cookie::create(
            $name ?: $this->defaultName,
            $jwt,
            $expiresAt,
            $path ?: $this->defaultPath,
            $domain ?: $this->defaultDomain,
            $secure ?: $this->defaultSecure,
            $httpOnly ?: $this->defaultHttpOnly,
            false,
            $sameSite ?: $this->defaultSameSite
        );
    }
}
