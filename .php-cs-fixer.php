<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
       'src',
       'tests',
    ]);

$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        '@PHP81Migration' => true,
        '@PSR12' => true,
        '@PSR12:risky' => true,
        'array_indentation' => true,
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'blank_line_after_namespace' => true,
        'final_class' => true,
        'fully_qualified_strict_types' => true,
        'no_closing_tag' => true,
        'no_empty_phpdoc' => true,
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
        ],
        'no_unused_imports' => true,
        'no_useless_return' => true,
        'not_operator_with_successor_space' => true,
        'ordered_imports' => true,
        'php_unit_internal_class' => true,
        'php_unit_method_casing' => [
            'case' => 'snake_case',
        ],
        'php_unit_set_up_tear_down_visibility' => true,
        'php_unit_test_annotation' => [
            'style' => 'annotation',
        ],
        'simplified_null_return' => true,
        'single_blank_line_before_namespace' => true,
        'single_blank_line_at_eof' => true,
        'single_line_after_imports' => true,
        'single_quote' => true,
        'trailing_comma_in_multiline' => true,
    ])
    ->setFinder($finder);
