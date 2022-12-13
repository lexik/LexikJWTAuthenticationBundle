<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Command;

use ApiPlatform\Symfony\Bundle\ApiPlatformBundle;
use Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Check API Platform compatibility.
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * @requires function ApiPlatformBundle::build
 */
class ApiPlatformOpenApiExportCommandTest extends TestCase
{
    /**
     * Test command.
     */
    public function testCheckOpenApiExportCommand()
    {
        $kernel = $this->bootKernel();
        $app = new Application($kernel);
        $tester = new CommandTester($app->find('api:openapi:export'));

        $this->assertSame(0, $tester->execute([]));
        $this->assertJsonStringEqualsJsonString(<<<JSON
{
  "openapi": "3.0.0",
  "info": {
    "description": "API Platform integration in LexikJWTAuthenticationBundle",
    "title": "LexikJWTAuthenticationBundle",
    "version": "1.0.0"
  },
  "servers": [
    {
      "description": "",
      "url": "/"
    }
  ],
  "paths": {
    "/login_check": {
      "post": {
        "deprecated": false,
        "description": "",
        "operationId": "login_check_post",
        "parameters": [],
        "requestBody": {
          "description": "The login data",
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "type": "object",
                "properties": {
                  "_username": {
                    "nullable": false,
                    "type": "string"
                  },
                  "_password": {
                    "nullable": false,
                    "type": "string"
                  }
                },
                "required": [
                  "_username",
                  "_password"
                ]
              }
            }
          }
        },
        "responses": {
          "200": {
            "content": {
              "application/json": {
                "schema": {
                  "properties": {
                    "token": {
                      "nullable": false,
                      "readOnly": true,
                      "type": "string"
                    }
                  },
                  "required": [
                    "token"
                  ],
                  "type": "object"
                }
              }
            },
            "description": "User token created"
          }
        },
        "summary": "Creates a user token.",
        "tags": [
          "Login Check"
        ]
      },
      "parameters": []
    }
  },
  "components": {
    "examples": {},
    "headers": {},
    "parameters": {},
    "requestBodies": {},
    "responses": {},
    "schemas": {},
    "securitySchemes": {}
  },
  "security": [],
  "tags": []
}
JSON
            , $tester->getDisplay());
    }
}
