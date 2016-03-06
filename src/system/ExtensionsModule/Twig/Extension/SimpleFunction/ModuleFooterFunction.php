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

namespace Zikula\ExtensionsModule\Twig\Extension\SimpleFunction;

use Symfony\Component\HttpKernel\Controller\ControllerReference;

class ModuleFooterFunction
{
    private $handler;

    /**
     * ModuleFooterFunction constructor.
     */
    public function __construct($handler)
    {
        $this->handler = $handler;
    }

    /**
     * Inserts module footer.
     *
     * This has NO configuration options.
     *
     * Examples:
     *
     * <samp>{( moduleFooter() }}</samp>
     *
     * @return string
     */
    public function display()
    {
        $ref = new ControllerReference('ZikulaExtensionsModule:ExtensionsInterface:footer');

        return $this->handler->render($ref, 'inline', []);
    }
}
