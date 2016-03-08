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

namespace Zikula\AdminModule\Twig\Extension\SimpleFunction;

use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

class AdminBreadcrumbsFunction
{
    /**
     * @var FragmentHandler
     */
    private $handler;

    /**
     * AdminBreadcrumbsFunction constructor.
     * @param FragmentHandler $handler
     */
    public function __construct(FragmentHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Inserts admin breadcrumbs.
     *
     * This has NO configuration options.
     *
     * Examples:
     *
     * <samp>{{ adminBreadcrumbs() }}</samp>
     *
     * @return string
     */
    public function display()
    {
        $ref = new ControllerReference('ZikulaAdminModule:AdminInterface:breadcrumbs');

        return $this->handler->render($ref, 'inline', []);
    }
}
