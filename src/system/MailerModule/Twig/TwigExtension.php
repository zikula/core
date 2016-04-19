<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
     * @return boolean True if proc_* functions are available, false otherwise.
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
