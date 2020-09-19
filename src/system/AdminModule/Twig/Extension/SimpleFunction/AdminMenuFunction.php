<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
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

    public function __construct(FragmentHandler $handler, PermissionApiInterface $permissionApi)
    {
        $this->handler = $handler;
        $this->permissionApi = $permissionApi;
    }

    /**
     * Inserts admin menu based on mode.
     *
     * Example: {( adminMenu() }}
     *
     * @param string $mode 'modules'|'categories' - gets menu organized by modules or it's admin categories
     * @param string $template 'tabs'|'panel'
     */
    public function display(string $mode = 'categories', string $template = 'tabs'): string
    {
        if (!$this->permissionApi->hasPermission('ZikulaAdminModule::', '::', ACCESS_EDIT)) {
            return ''; // Since no permission, return empty
        }

        $ref = new ControllerReference('Zikula\AdminModule\Controller\AdminInterfaceController::menu', [
            'mode' => $mode,
            'template' => $template
        ]);

        return $this->handler->render($ref) ?? '';
    }
}
