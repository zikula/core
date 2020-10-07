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

namespace Zikula\Bundle\CoreBundle\Maker;

use InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use function Symfony\Component\String\s;

class Validators
{
    public static function validateBundleNamespace(InputInterface $input, $allowSuffix = false): string
    {
        $namespace = s($input->getArgument('namespace'))->replace('/', '\\')->trim()->toString();
        if (!preg_match('/^(?:[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\\\?)+$/', $namespace)) {
            throw new InvalidArgumentException('The namespace contains invalid characters.');
        }

        // validate reserved keywords
        $reserved = self::getReservedWords();
        foreach (explode('\\', $namespace) as $word) {
            if (in_array(s($word)->lower()->toString(), $reserved, true)) {
                throw new InvalidArgumentException(sprintf('The namespace cannot contain reserved words ("%s").', $word));
            }
        }

        if (!$allowSuffix) {
            $reserved = ['module', 'theme', 'bundle'];
            if (s($namespace)->lower()->containsAny($reserved)) {
                throw new InvalidArgumentException(sprintf('The namespace cannot contain "%s" suffixes.', implode(' | ', $reserved)));
            }
        }

        if (s($namespace)->startsWith('Zikula\\') && false === $input->getOption('force')) {
            throw new InvalidArgumentException(sprintf('Use of Zikula as the vendor name is not recommended. If you use it you must also specify the %s option', '--force'));
        }

        // validate that the namespace is at least one level deep
        if (null === s($namespace)->indexOf('\\')) {
            throw new InvalidArgumentException(sprintf('The namespace must contain a vendor namespace (e.g. "VendorName\%s" instead of simply "%s").', $namespace, $namespace));
        }

        if (1 < mb_substr_count($namespace, '\\')) {
            throw new InvalidArgumentException(sprintf('The namespace must contain only a vendor and BundleName (e.g. "VendorName\BundleName" instead of "%s").', $namespace));
        }

        return $namespace;
    }

    public static function getReservedWords(): array
    {
        return [
            'abstract',
            'and',
            'array',
            'as',
            'break',
            'case',
            'catch',
            'class',
            'clone',
            'const',
            'continue',
            'declare',
            'default',
            'do',
            'else',
            'elseif',
            'enddeclare',
            'endfor',
            'endforeach',
            'endif',
            'endswitch',
            'endwhile',
            'extends',
            'final',
            'for',
            'foreach',
            'function',
            'global',
            'goto',
            'if',
            'implements',
            'interface',
            'instanceof',
            'namespace',
            'new',
            'or',
            'private',
            'protected',
            'public',
            'static',
            'switch',
            'throw',
            'try',
            'use',
            'var',
            'while',
            'xor',
            '__CLASS__',
            '__DIR__',
            '__FILE__',
            '__LINE__',
            '__FUNCTION__',
            '__METHOD__',
            '__NAMESPACE__',
            'die',
            'echo',
            'empty',
            'exit',
            'eval',
            'include',
            'include_once',
            'isset',
            'list',
            'require',
            'require_once',
            'return',
            'print',
            'unset'
        ];
    }
}
