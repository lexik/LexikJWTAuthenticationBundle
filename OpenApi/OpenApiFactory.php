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
    /**
     * @var OpenApiFactoryInterface
     */
    private $decorated;

    private $checkPath;

    public function __construct(OpenApiFactoryInterface $decorated, string $checkPath)
    {
        $this->decorated = $decorated;
        $this->checkPath = $checkPath;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);

        $openApi
            ->getPaths()
            ->addPath($this->checkPath, (new PathItem())->withPost((new Operation())
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
                ->withRequestBody((new RequestBody())
                    ->withDescription('The login data')
                    ->withContent(new \ArrayObject([
                        'application/json' => new MediaType(new \ArrayObject(new \ArrayObject([
                            'type' => 'object',
                            'properties' => [
                                '_username' => [
                                    'type' => 'string',
                                    'nullable' => false,
                                ],
                                '_password' => [
                                    'type' => 'string',
                                    'nullable' => false,
                                ],
                            ],
                            'required' => ['_username', '_password'],
                        ]))),
                    ]))
                    ->withRequired(true)
                )
            ));

        return $openApi;
    }
}
