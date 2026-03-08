<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->name('*.php')
    ->notPath('vendor');

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12'                        => true,
        '@PHP81Migration'               => true,
        'array_syntax'                  => ['syntax' => 'short'],
        'ordered_imports'               => ['sort_algorithm' => 'alpha'],
        'no_unused_imports'             => true,
        'declare_strict_types'          => true,
        'trailing_comma_in_multiline'   => ['elements' => ['arrays', 'parameters', 'arguments']],
        'no_trailing_whitespace'        => true,
        'blank_line_after_namespace'    => true,
        'blank_line_after_opening_tag'  => true,
        'single_quote'                  => true,
        'binary_operator_spaces'        => ['default' => 'align_single_space_minimal'],
        'cast_spaces'                   => ['space' => 'single'],
        'concat_space'                  => ['spacing' => 'one'],
        'method_argument_space'         => ['on_multiline' => 'ensure_fully_multiline'],
        'phpdoc_align'                  => ['align' => 'vertical'],
        'phpdoc_trim'                   => true,
        'no_superfluous_phpdoc_tags'    => true,
    ])
    ->setFinder($finder);
