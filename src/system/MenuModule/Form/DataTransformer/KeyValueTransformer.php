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

namespace Zikula\MenuModule\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class KeyValueTransformer implements DataTransformerInterface
{
    /**
     * Does nothing. This is handled by the Event Listener instead (a quirk of Symfony).
     *
     * @param  array $array
     * @return array
     */
    public function transform($array)
    {
        return $array;
    }

    /**
     * Transforms an array with keys of 'key' and 'value' to key-value pairs.
     * Where the value is a json_encoded array, string, it decodes the string first.
     *
     * @param  array $array
     * @return array
     */
    public function reverseTransform($array)
    {
        $return = [];
        foreach ($array as $optionDef) {
            $return[$optionDef['key']] = $optionDef['value'][0] === '{' ? json_decode($optionDef['value'], true) : $optionDef['value'];
        }

        return $return;
    }
}
