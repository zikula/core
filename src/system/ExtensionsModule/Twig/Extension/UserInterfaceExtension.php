<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
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

namespace Zikula\ExtensionsModule\Twig\Extension;

use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Zikula\ExtensionsModule\Twig\Extension\SimpleFunction\ModuleHeaderFunction;
use Zikula\ExtensionsModule\Twig\Extension\SimpleFunction\ModuleLinksFunction;
use Zikula\ExtensionsModule\Twig\Extension\SimpleFunction\ModuleFooterFunction;
use Zikula\ExtensionsModule\Twig\Extension\SimpleFunction\ModuleHelpFunction;

class UserInterfaceExtension extends \Twig_Extension
{
    private $handler;

    /**
     * constructor.
     */
    public function __construct(FragmentHandler $handler)
    {
        $this->handler = $handler;
    }

    public function getName()
    {
        return 'zikulaextensionsmodule.admin_interface';
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
                new \Twig_SimpleFunction('moduleHeader', [new ModuleHeaderFunction($this->handler), 'display'], ['is_safe' => array('html')]),
                new \Twig_SimpleFunction('moduleLinks', [new ModuleLinksFunction($this->handler), 'display'], ['is_safe' => array('html')]),
                new \Twig_SimpleFunction('moduleHelp', [new ModuleHelpFunction($this->handler), 'display'], ['is_safe' => array('html')]),
                new \Twig_SimpleFunction('moduleFooter', [new ModuleFooterFunction($this->handler), 'display'], ['is_safe' => array('html')]),
        );
    }
}
