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
 * Inline implements a YAML parser/dumper for the YAML inline syntax.
 */
class Inline extends \Symfony\Component\Yaml\Inline
{
    /**
     * {@inheritdoc}
     */
    public static function dump($value, $exceptionOnInvalidType = false, $objectSupport = false)
    {
        switch (true) {
            case is_array($value):
                return self::dumpArray($value, $exceptionOnInvalidType, $objectSupport);
            default:
                return parent::dump($value, $exceptionOnInvalidType, $objectSupport);
        }
    }

    /**
     * {@inheritdoc}
     */
    private static function dumpArray($value, $exceptionOnInvalidType, $objectSupport)
    {
        // array
        // HACK for #2889
        if (/*$value && */!self::isHash($value)) {
            $output = [];
            foreach ($value as $val) {
                $output[] = self::dump($val, $exceptionOnInvalidType, $objectSupport);
            }

            return sprintf('[%s]', implode(', ', $output));
        }

        // hash
        $output = [];
        foreach ($value as $key => $val) {
            $output[] = sprintf('%s: %s', self::dump($key, $exceptionOnInvalidType, $objectSupport), self::dump($val, $exceptionOnInvalidType, $objectSupport));
        }

        return sprintf('{ %s }', implode(', ', $output));
    }
}
