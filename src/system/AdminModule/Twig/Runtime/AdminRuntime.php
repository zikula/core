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

namespace Zikula\AdminModule\Twig\Runtime;

use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Twig\Extension\RuntimeExtensionInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class AdminRuntime implements RuntimeExtensionInterface
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

    public function adminBreadcrumbs(): string
    {
        $ref = new ControllerReference('Zikula\AdminModule\Controller\AdminInterfaceController::breadcrumbs');

        return $this->handler->render($ref) ?? '';
    }

    public function adminFooter(): string
    {
        $ref = new ControllerReference('Zikula\AdminModule\Controller\AdminInterfaceController::footer');

        return $this->handler->render($ref) ?? '';
    }

    public function adminHeader(): string
    {
        $ref = new ControllerReference('Zikula\AdminModule\Controller\AdminInterfaceController::header');

        return $this->handler->render($ref) ?? '';
    }

    public function adminMenu(string $mode = 'categories', string $template = 'tabs'): string
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

    public function adminPanelMenu(string $mode = 'modules', string $template = 'panel'): string
    {
        return $this->adminMenu($mode, $template);
    }

    public function adminSecurityAnalyzer(): string
    {
        $ref = new ControllerReference('Zikula\AdminModule\Controller\AdminInterfaceController::securityanalyzer');

        return $this->handler->render($ref) ?? '';
    }

    public function adminUpdateCheck(): string
    {
        $ref = new ControllerReference('Zikula\AdminModule\Controller\AdminInterfaceController::updatecheck');

        return $this->handler->render($ref) ?? '';
    }
}
