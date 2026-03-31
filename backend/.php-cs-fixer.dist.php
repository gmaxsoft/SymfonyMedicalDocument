<?php

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__.'/src',
        __DIR__.'/migrations',
        __DIR__.'/config',
    ])
    ->filter(static fn (\SplFileInfo $f): bool => !str_ends_with($f->getFilename(), 'reference.php'))
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'declare_strict_types' => true,
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => true, 'import_functions' => true],
    ])
    ->setFinder($finder)
;
