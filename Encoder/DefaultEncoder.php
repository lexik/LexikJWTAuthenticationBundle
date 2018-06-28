<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Encoder;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Namshi\JOSE\JWS;

/**
 * Default Json Web Token encoder/decoder.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 *
 * @deprecated since version 2.5, to be removed in 3.0
 */
class DefaultEncoder extends LcobucciJWTEncoder
{
    public function __construct(JWSProviderInterface $jwsProvider)
    {
        if (!class_exists(JWS::class)) {
            throw new \RuntimeException('The "namshi/jose" library is deprecated, this bundle does not require it anymore. If you need to keep using it, require it in your composer.json.');
        }

        @trigger_error(sprintf('The "%s\DefaultEncoder" class is deprecated since version 2.5 and will be removed in 3.0. Use "%s" or create your own encoder instead.', __NAMESPACE__, LcobucciJWTEncoder::class), E_USER_DEPRECATED);

        parent::__construct($jwsProvider);
    }
}
