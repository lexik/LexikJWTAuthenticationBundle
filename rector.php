<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\AddSeeTestAnnotationRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonyLevelSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_71,
        SymfonyLevelSetList::UP_TO_SYMFONY_44,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
    ]);
    $rectorConfig->phpVersion(PhpVersion::PHP_71);
    $rectorConfig->importShortClasses(false);
    $rectorConfig->importNames();
    $rectorConfig->bootstrapFiles([
        __DIR__ . '/vendor/autoload.php',
    ]);
    $rectorConfig->parallel();
    $rectorConfig->paths([__DIR__]);
    $rectorConfig->skip([
        // Path
        __DIR__ . '/.github',
        __DIR__ . '/DependencyInjection/Configuration.php',
        __DIR__ . '/Tests/DependencyInjection/LexikJWTAuthenticationExtensionTest.php',
        __DIR__ . '/vendor',

        // Rules
        AddSeeTestAnnotationRector::class,
        JsonThrowOnErrorRector::class,
        ReturnNeverTypeRector::class => [
            __DIR__ . '/Security/User/JWTUserProvider.php',
        ],
    ]);
};
