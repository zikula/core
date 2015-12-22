<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\FormExtensionBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Workaround for Symfony bug #5906
 * @see https://github.com/symfony/symfony/issues/5906
 * @author b.b3rn4ard
 * @see http://stackoverflow.com/a/28889445/2600812
 * Class NullToEmptyTransformer
 * @package Zikula\Bundle\FormExtensionBundle\Form\DataTransformer
 */
class NullToEmptyTransformer implements DataTransformerInterface
{
    /**
     * Does not transform anything
     *
     * @param  string|null $value
     * @return string
     */
    public function transform($value)
    {
        return $value;
    }

    /**
     * Transforms a null to an empty string.
     *
     * @param  string $value
     * @return string
     */
    public function reverseTransform($value)
    {
        if (is_null($value)) {
            return '';
        }

        return $value;
    }
}
