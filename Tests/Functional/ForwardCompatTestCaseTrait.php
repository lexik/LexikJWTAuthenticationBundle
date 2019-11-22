<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

if (70000 <= \PHP_VERSION_ID && (new \ReflectionMethod(WebTestCase::class, 'tearDown'))->hasReturnType()) {
    eval('
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
        }
    ');
} else {
    /**
     * @internal
     */
    trait ForwardCompatTestCaseTrait
    {
        protected function tearDown()
        {
            static::ensureKernelShutdown();
            static::$kernel = null;
        }
    }
}
