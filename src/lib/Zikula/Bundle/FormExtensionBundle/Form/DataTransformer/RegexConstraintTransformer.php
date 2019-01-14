<?php

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
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Class RegexConstraintTransformer
 */
class RegexConstraintTransformer implements DataTransformerInterface
{
    /**
     * Transforms constraint into the text pattern.
     *
     * @param array $value
     * @return string
     */
    public function transform($value)
    {
        /** @var Regex $constraint */
        $constraint = is_array($value) && isset($value[0]) ? $value[0] : new Regex('/.*/');

        return $constraint->pattern;
    }

    /**
     * Transforms a regex pattern into an array of constraints.
     *
     * @param string $value
     * @return array
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            $value = new Regex('/.*/');
        }

        return [new Regex($value)];
    }
}
