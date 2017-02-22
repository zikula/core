<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Yaml;

/**
 * Dumper dumps PHP variables to YAML strings.
 *
 * @todo remove after migration to Symfony 3.x and use new parameters instead
 * @see https://github.com/zikula/core/issues/2889
 * @see http://symfony.com/blog/new-in-symfony-3-1-customizable-yaml-parsing-and-dumping
 */
class Dumper extends \Symfony\Component\Yaml\Dumper
{
    /**
     * {@inheritdoc}
     */
    public function dump($input, $inline = 0, $indent = 0, $exceptionOnInvalidType = false, $objectSupport = false)
    {
        $output = '';
        $prefix = $indent ? str_repeat(' ', $indent) : '';

        if ($inline <= 0 || !is_array($input) || empty($input)) {
            $output .= $prefix . Inline::dump($input, $exceptionOnInvalidType, $objectSupport);
        } else {
            $isAHash = Inline::isHash($input);

            foreach ($input as $key => $value) {
                $willBeInlined = $inline - 1 <= 0 || !is_array($value) || empty($value);

                $output .= sprintf('%s%s%s%s',
                    $prefix,
                    $isAHash ? Inline::dump($key, $exceptionOnInvalidType, $objectSupport) . ':' : '-',
                    $willBeInlined ? ' ' : "\n",
                    $this->dump($value, $inline - 1, $willBeInlined ? 0 : $indent + $this->indentation, $exceptionOnInvalidType, $objectSupport)
                ) . ($willBeInlined ? "\n" : '');
            }
        }

        return $output;
    }
}
