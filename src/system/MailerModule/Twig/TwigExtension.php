<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MailerModule\Twig;

/**
 * Twig extension class.
 */
class TwigExtension extends \Twig_Extension
{
    /**
     * Returns a list of custom Twig functions.
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('zikulamailermodule_hasProcessControlFunctions', [$this, 'hasProcessControlFunctions'])
        ];
    }

    /**
     * The zikulamailermodule_hasProcessControlFunctions function determines whether the proc_* functions are available.
     *
     * @return boolean True if proc_* functions are available, false otherwise
     */
    public function hasProcessControlFunctions()
    {
        return function_exists('proc_open');
    }

    /**
     * Returns internal name of this extension.
     *
     * @return string
     */
    public function getName()
    {
        return 'zikulamailermodule_twigextension';
    }
}
