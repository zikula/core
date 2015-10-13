<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 * @package SpecTheme
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\SeaBreezeTheme\Twig;

class SeaBreezeThemeExtension extends \Twig_Extension
{
    public function __construct()
    {
    }

    public function getName()
    {
        return 'seabreezetheme_extension';
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('urldecode', [$this, 'urldecode'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param $string
     * @return string
     */
    public function urldecode($string)
    {
        return urldecode($string);
    }
}