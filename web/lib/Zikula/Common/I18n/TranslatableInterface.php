<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Common\I18n;

/**
 * Zikula Translate interface.
 */
interface TranslatableInterface
{
    /**
     * singular translation for modules.
     *
     * @param string $msg Message.
     *
     * @return string
     */
    function __($msg);

    /**
     * Plural translations for modules.
     *
     * @param string  $m1 Singular.
     * @param string  $m2 Plural.
     * @param integer $n  Count.
     *
     * @return string
     */
    function _n($m1, $m2, $n);

    /**
     * Format translations for modules.
     *
     * @param string       $msg   Message.
     * @param string|array $param Format parameters.
     *
     * @return string
     */
    function __f($msg, $param);

    /**
     * Format pural translations for modules.
     *
     * @param string       $m1    Singular.
     * @param string       $m2    Plural.
     * @param integer      $n     Count.
     * @param string|array $param Format parameters.
     *
     * @return string
     */
    function _fn($m1, $m2, $n, $param);
}