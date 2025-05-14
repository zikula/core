<?php

declare(strict_types=1);

use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

if (!file_exists(__DIR__)) {
    exit(0);
}

// see also https://github.com/symfony/symfony/blob/7.3/.php-cs-fixer.dist.php

return new PhpCsFixer\Config()
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRules([
        '@PSR12' => true,
        '@PHP84Migration' => true,
        '@PHPUnit100Migration:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'combine_nested_dirname' => true,
        'concat_space' => ['spacing' => 'one'],
        'fopen_flags' => false,
        'mb_str_functions' => true,
        'native_constant_invocation' => false,
        'native_function_invocation' => false,
        'no_short_bool_cast' => true,
        'no_unreachable_default_argument_value' => false,
        'nullable_type_declaration_for_default_null_value' => true,
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_to_param_type' => true,
        'phpdoc_to_return_type' => true,
        'php_unit_test_annotation' => false, // breaks "@depends App\Something::testFooBar()"
        'protected_to_private' => false,
        'simplified_null_return' => true,
        'single_line_throw' => false,
    ])
    ->setRiskyAllowed(true)
    ->setFinder(
        new PhpCsFixer\Finder()
            ->in(['src', 'tests'])
            ->notPath('#/Fixtures/#')
            ->notPath('#/vendor/#')
    )
    ->setCacheFile('.php-cs-fixer.cache')
;
