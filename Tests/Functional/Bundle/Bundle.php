<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Bundle;

use Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Bundle\DependencyInjection\BundleExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle as BaseBundle;

class Bundle extends BaseBundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new BundleExtension();
    }
}
