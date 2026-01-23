<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

// see also https://github.com/symfony/symfony/blob/7.4/.php-cs-fixer.dist.php

return new PhpCsFixer\Config()
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRules([
        '@PER-CS' => true,
        '@PHP8x5Migration' => true,
        '@PHPUnit11x0Migration:risky' => true,
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
        'no_useless_else' => true,
        'no_useless_return' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_to_param_type' => true,
        'phpdoc_to_return_type' => true,
        'php_unit_attributes' => true,
        'php_unit_test_annotation' => false, // breaks "@depends App\Something::testFooBar()"
        'protected_to_private' => false,
        'simplified_null_return' => true,
        'single_line_throw' => false,
        'static_lambda' => true,
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
