<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\MediaType;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\OpenApi;
use Symfony\Component\HttpFoundation\Response;

/**
 * Decorates API Platform OpenApiFactory.
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * @final
 */
class OpenApiFactory implements OpenApiFactoryInterface
{
    private OpenApiFactoryInterface $decorated;
    private string $checkPath;
    private string $usernamePath;
    private string $passwordPath;

    public function __construct(OpenApiFactoryInterface $decorated, string $checkPath, string $usernamePath, string $passwordPath)
    {
        $this->decorated = $decorated;
        $this->checkPath = $checkPath;
        $this->usernamePath = $usernamePath;
        $this->passwordPath = $passwordPath;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);

        $openApi
            ->getComponents()->getSecuritySchemes()->offsetSet(
                'JWT',
                new \ArrayObject(
                    [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT',
                    ]
                )
            );

        $openApi
            ->getPaths()
            ->addPath($this->checkPath, (new PathItem())->withPost(
                (new Operation())
                ->withOperationId('login_check_post')
                ->withTags(['Login Check'])
                ->withResponses([
                    Response::HTTP_OK => [
                        'description' => 'User token created',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'token' => [
                                            'readOnly' => true,
                                            'type' => 'string',
                                            'nullable' => false,
                                        ],
                                    ],
                                    'required' => ['token'],
                                ],
                            ],
                        ],
                    ],
                ])
                ->withSummary('Creates a user token.')
                ->withDescription('Creates a user token.')
                ->withRequestBody(
                    (new RequestBody())
                    ->withDescription('The login data')
                    ->withContent(new \ArrayObject([
                        'application/json' => new MediaType(new \ArrayObject([
                            'type' => 'object',
                            'properties' => $properties = array_merge_recursive($this->getJsonSchemaFromPathParts(explode('.', $this->usernamePath)), $this->getJsonSchemaFromPathParts(explode('.', $this->passwordPath))),
                            'required' => array_keys($properties),
                        ])),
                    ]))
                    ->withRequired(true)
                )
            ));

        return $openApi;
    }

    private function getJsonSchemaFromPathParts(array $pathParts): array
    {
        $jsonSchema = [];

        if (count($pathParts) === 1) {
            $jsonSchema[array_shift($pathParts)] = [
                'type' => 'string',
                'nullable' => false,
            ];

            return $jsonSchema;
        }

        $pathPart = array_shift($pathParts);
        $properties = $this->getJsonSchemaFromPathParts($pathParts);
        $jsonSchema[$pathPart] = [
            'type' => 'object',
            'properties' => $properties,
            'required' => array_keys($properties),
        ];

        return $jsonSchema;
    }
}
