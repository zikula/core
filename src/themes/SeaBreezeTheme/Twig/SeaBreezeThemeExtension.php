<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SeaBreezeTheme\Twig;

class SeaBreezeThemeExtension extends \Twig_Extension
{
    public function __construct()
    {
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
