<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Cookie;

use Symfony\Component\HttpFoundation\Cookie;

/**
 * Creates secure JWT cookies.
 */
final class JWTCookieProvider
{
    private $defaultName;
    private $defaultLifetime;
    private $defaultSameSite;
    private $defaultPath;
    private $defaultDomain;
    private $defaultSecure;

    /**
     * @param string|null $defaultName
     * @param int|null    $defaultLifetime
     * @param string      $defaultPath
     * @param string|null $defaultDomain
     * @param string      $defaultSameSite
     * @param bool        $defaultSecure
     */
    public function __construct($defaultName = null, $defaultLifetime = 0, $defaultSameSite = Cookie::SAMESITE_LAX, $defaultPath = '/', $defaultDomain = null, $defaultSecure = true)
    {
        $this->defaultName = $defaultName;
        $this->defaultLifetime = $defaultLifetime;
        $this->defaultSameSite = $defaultSameSite;
        $this->defaultPath = $defaultPath;
        $this->defaultDomain = $defaultDomain;
        $this->defaultSecure = $defaultSecure;
    }

    /**
     * Creates a secure cookie containing the passed JWT.
     *
     * For each argument (all args except $jwt), if it is omitted or set to null then the
     * default value defined via the constructor will be used.
     *
     * @param string                             $jwt
     * @param string|null                        $name
     * @param int|string|\DateTimeInterface|null $expiresAt
     * @param string|null                        $sameSite
     * @param string|null                        $path
     * @param string|null                        $domain
     * @param bool|null                          $secure
     *
     * @return Cookie
     */
    public function createCookie($jwt, $name = null, $expiresAt = null, $sameSite = null, $path = null, $domain = null, $secure = null)
    {
        if (!$name && !$this->defaultName) {
            throw new \LogicException(sprintf('The cookie name must be provided, either pass it as 2nd argument of %s or set a default name via the constructor.', __METHOD__));
        }

        if (!$expiresAt && !$this->defaultLifetime) {
            throw new \LogicException(sprintf('The cookie expiration time must be provided, either pass it as 3rd argument of %s or set a default lifetime via the constructor.', __METHOD__));
        }

        return new Cookie(
            $name ?: $this->defaultName,
            $jwt,
            null === $expiresAt ? (time() + $this->defaultLifetime) : $expiresAt,
            $path ?: $this->defaultPath,
            $domain ?: $this->defaultDomain,
            $secure ?: $this->defaultSecure,
            true,
            false,
            $sameSite ?: $this->defaultSameSite
        );
    }
}
