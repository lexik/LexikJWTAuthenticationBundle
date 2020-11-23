<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests;

use PHPUnit\Framework\TestCase;

if (70000 <= \PHP_VERSION_ID && (new \ReflectionMethod(TestCase::class, 'setUp'))->hasReturnType()) {
    eval('
        namespace Lexik\Bundle\JWTAuthenticationBundle\Tests;

        /**
         * @internal
         */
        trait ForwardCompatTestCaseTrait
        {
            protected function setUp(): void
            {
                parent::setUp();
            
                $this->doSetUp();
            }
                        
            protected function doSetUp()
            {
            }
            
            protected function tearDown(): void
            {
                parent::tearDown();
            
                $this->doTearDown();
            }
                        
            protected function doTearDown()
            {
            }
        }
    ');
} else {
    /**
     * @internal
     */
    trait ForwardCompatTestCaseTrait
    {
        protected function setUp()
        {
            parent::setUp();

            $this->doSetUp();
        }

        protected function doSetUp()
        {
        }

        protected function tearDown()
        {
            parent::tearDown();

            $this->doTearDown();
        }

        protected function doTearDown()
        {
        }
    }
}
