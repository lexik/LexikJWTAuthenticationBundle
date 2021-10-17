<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional;

/**
 * @internal
 */
trait ForwardCompatTestCaseTrait
{
    protected function tearDown(): void
    {
        static::ensureKernelShutdown();
        static::$kernel = null;
    }

    protected function setUp(): void
    {
        $this->doSetUp();
    }
}
