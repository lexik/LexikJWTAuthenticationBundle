<?php

$finder = PhpCsFixer\Finder::create()->in([__DIR__]);

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        'ordered_imports' => true,
        'array_syntax' => ['syntax' => 'short'],
        'phpdoc_scalar' => false,
    ])
    ->setUsingCache(false)
    ->setFinder($finder);
;
