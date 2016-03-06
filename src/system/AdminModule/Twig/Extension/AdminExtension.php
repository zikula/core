<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 * @package ZikulaAdminModule
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\AdminModule\Twig\Extension;

use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Zikula\AdminModule\Twig\Extension\SimpleFunction\AdminHeaderFunction;
use Zikula\AdminModule\Twig\Extension\SimpleFunction\AdminBreadcrumbsFunction;
use Zikula\AdminModule\Twig\Extension\SimpleFunction\AdminUpdateCheckFunction;
use Zikula\AdminModule\Twig\Extension\SimpleFunction\AdminDeveloperNoticesFunction;
use Zikula\AdminModule\Twig\Extension\SimpleFunction\AdminSecurityAnalyzerFunction;
use Zikula\AdminModule\Twig\Extension\SimpleFunction\AdminMenuFunction;
use Zikula\AdminModule\Twig\Extension\SimpleFunction\AdminFooterFunction;

class AdminExtension extends \Twig_Extension
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
        return 'zikula_admin_module';
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
                new \Twig_SimpleFunction('adminHeader', [new AdminHeaderFunction($this->handler), 'display'], ['is_safe' => array('html')]),
                new \Twig_SimpleFunction('adminBreadcrumbs', [new AdminBreadcrumbsFunction($this->handler), 'display'], ['is_safe' => array('html')]),
                new \Twig_SimpleFunction('adminUpdateCheck', [new AdminUpdateCheckFunction($this->handler), 'display'], ['is_safe' => array('html')]),
                new \Twig_SimpleFunction('adminDeveloperNotices', [new AdminDeveloperNoticesFunction($this->handler), 'display'], ['is_safe' => array('html')]),
                new \Twig_SimpleFunction('adminSecurityAnalyzer', [new AdminSecurityAnalyzerFunction($this->handler), 'display'], ['is_safe' => array('html')]),
                new \Twig_SimpleFunction('adminMenu', [new AdminMenuFunction($this->handler), 'display'], ['is_safe' => array('html')]),
                new \Twig_SimpleFunction('adminPanelMenu', [$this, 'adminPanelMenu'], ['is_safe' => array('html')]),
                new \Twig_SimpleFunction('adminFooter', [new AdminFooterFunction($this->handler), 'display'], ['is_safe' => array('html')]),
        );
    }

    public function adminPanelMenu($mode = 'modules', $template = 'panel')
    {
        $adminMenu = new AdminMenuFunction($this->handler);

        return $adminMenu->display($mode, $template);
    }
}
