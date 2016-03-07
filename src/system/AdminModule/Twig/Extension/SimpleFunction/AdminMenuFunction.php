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
use Zikula\PermissionsModule\Api\PermissionApi;

class AdminMenuFunction
{
    /**
     * @var FragmentHandler
     */
    private $handler;
    /**
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * AdminMenuFunction constructor.
     * @param FragmentHandler $handler
     */
    public function __construct(FragmentHandler $handler, PermissionApi $permissionApi)
    {
        $this->handler = $handler;
        $this->permissionApi = $permissionApi;
    }

    /**
     * Inserts admin menu based on mode.
     *
     * Examples:
     *
     * <samp>{( adminMenu() }}</samp>
     *
     * @param string $mode modules/categories - gets menu organized by modules or it's admin categories
     * @param string $template
     * @return string
     */
    public function display($mode = 'categories', $template = 'tabs')
    {
        if (!$this->permissionApi->hasPermission('ZikulaAdminModule::', "::", ACCESS_EDIT)) {
            return ''; // Since no permission, return empty
        }

        $ref = new ControllerReference('ZikulaAdminModule:AdminInterface:menu', array('mode' => $mode, 'template' => $template));

        return $this->handler->render($ref, 'inline', []);
    }
}
