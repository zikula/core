<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Component\FilterUtil;

/**
 * FilterUtil replace interface
 */
interface ReplaceInterface
{
    /**
     * Replace whatever the plugin has to replace.
     *
     * @param string $field Field name.
     * @param string $op    Operator.
     * @param string $value Value.
     *
     * @return array ($field, $op, $value)
     */
    public function replace($field, $op, $value);
}
