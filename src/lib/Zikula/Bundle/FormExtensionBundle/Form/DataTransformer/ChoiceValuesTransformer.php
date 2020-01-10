<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\FormExtensionBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class ChoiceValuesTransformer
 */
class ChoiceValuesTransformer implements DataTransformerInterface
{
    /**
     * Transforms choices array into a string.
     *
     * @param array $value
     * @return string
     */
    public function transform($value)
    {
        $strings = [];
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $strings[] = $k === $v ? $v : $v . ':' . $k;
            }
        }

        return implode(', ', $strings);
    }

    /**
     * Transforms the string back into a choices array .
     *
     * @param string $value
     * @return array
     */
    public function reverseTransform($value)
    {
        if (null === $value) {
            return [];
        }
        if (is_array($value)) {
            return $value;
        }
        $array = explode(',', $value);
        $newArray = [];
        foreach ($array as $v) {
            if (mb_strpos($v, ':')) {
                list($k, $v) = explode(':', $v);
            } else {
                $k = $v;
            }
            $newArray[trim($v)] = trim($k);
        }

        return $newArray;
    }
}
