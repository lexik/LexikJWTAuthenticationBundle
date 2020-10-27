<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Cookie;

use Lexik\Bundle\JWTAuthenticationBundle\Helper\JWTSplitter;
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
    private $defaultHttpOnly;
    private $defaultSplit;

    /**
     * @param string|null $defaultName
     * @param int|null    $defaultLifetime
     * @param string      $defaultPath
     * @param string|null $defaultDomain
     * @param string      $defaultSameSite
     * @param bool        $defaultSecure
     * @param bool        $defaultHttpOnly
     * @param array       $defaultSplit
     */
    public function __construct($defaultName = null, $defaultLifetime = 0, $defaultSameSite = Cookie::SAMESITE_LAX, $defaultPath = '/', $defaultDomain = null, $defaultSecure = true, $defaultHttpOnly = true, $defaultSplit = [])
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
     * @param bool|null                          $httpOnly
     * @param array                              $split
     *
     * @return Cookie
     */
    public function createCookie($jwt, $name = null, $expiresAt = null, $sameSite = null, $path = null, $domain = null, $secure = null, $httpOnly = null, $split = [])
    {
        if (!$name && !$this->defaultName) {
            throw new \LogicException(sprintf('The cookie name must be provided, either pass it as 2nd argument of %s or set a default name via the constructor.', __METHOD__));
        }

        if (!$expiresAt && !$this->defaultLifetime) {
            throw new \LogicException(sprintf('The cookie expiration time must be provided, either pass it as 3rd argument of %s or set a default lifetime via the constructor.', __METHOD__));
        }

        $jwtParts = new JWTSplitter($jwt);
        $jwt = $jwtParts->getParts($split ?: $this->defaultSplit);

        return new Cookie(
            $name ?: $this->defaultName,
            $jwt,
            null === $expiresAt ? (time() + $this->defaultLifetime) : $expiresAt,
            $path ?: $this->defaultPath,
            $domain ?: $this->defaultDomain,
            $secure ?: $this->defaultSecure,
            $httpOnly ?: $this->defaultHttpOnly,
            false,
            $sameSite ?: $this->defaultSameSite
        );
    }
}
