<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Twig\Extension\SimpleFunction;

use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class AdminMenuFunction
{
    /**
     * @var FragmentHandler
     */
    private $handler;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * AdminMenuFunction constructor.
     * @param FragmentHandler $handler
     * @param PermissionApiInterface $permissionApi
     */
    public function __construct(FragmentHandler $handler, PermissionApiInterface $permissionApi)
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
     * @param string $mode 'modules'|'categories' - gets menu organized by modules or it's admin categories
     * @param string $template 'tabs'|'panel'
     * @return string
     */
    public function display($mode = 'categories', $template = 'tabs')
    {
        if (!$this->permissionApi->hasPermission('ZikulaAdminModule::', '::', ACCESS_EDIT)) {
            return ''; // Since no permission, return empty
        }

        $ref = new ControllerReference('ZikulaAdminModule:AdminInterface:menu', [
            'mode' => $mode,
            'template' => $template
        ]);

        return $this->handler->render($ref, 'inline', []);
    }
}
