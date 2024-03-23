<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocScalarFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $config): void {
    $config->sets([SetList::PSR_12]);
    $config->rule(OrderedImportsFixer::class);
    $config->ruleWithConfiguration(ArraySyntaxFixer::class, [
        'syntax' => 'short',
    ]);

    $config->parallel();
    $config->paths([__DIR__]);
    $config->skip([
        __DIR__ . '/.github',
        __DIR__ . '/vendor',
        PhpdocScalarFixer::class
    ]);
};
