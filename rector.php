<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonyLevelSetList;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_71,
        SymfonyLevelSetList::UP_TO_SYMFONY_44,
        SymfonySetList::SYMFONY_STRICT,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
    ]);
    $rectorConfig->phpVersion(PhpVersion::PHP_71);
    $rectorConfig->importShortClasses();
    $rectorConfig->bootstrapFiles([
        __DIR__ . '/vendor/autoload.php',
    ]);
    $rectorConfig->paths([__DIR__]);
    $rectorConfig->skip([
        __DIR__ . '/.github',
        __DIR__ . '/Tests/DependencyInjection/LexikJWTAuthenticationExtensionTest.php',
        __DIR__ . '/vendor',
    ]);

    $services = $rectorConfig->services();
    $services->set(TypedPropertyRector::class);
};
