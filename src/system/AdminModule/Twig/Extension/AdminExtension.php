<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Twig\Extension;

use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Zikula\AdminModule\Twig\Extension\SimpleFunction\AdminBreadcrumbsFunction;
use Zikula\AdminModule\Twig\Extension\SimpleFunction\AdminFooterFunction;
use Zikula\AdminModule\Twig\Extension\SimpleFunction\AdminHeaderFunction;
use Zikula\AdminModule\Twig\Extension\SimpleFunction\AdminMenuFunction;
use Zikula\AdminModule\Twig\Extension\SimpleFunction\AdminSecurityAnalyzerFunction;
use Zikula\AdminModule\Twig\Extension\SimpleFunction\AdminUpdateCheckFunction;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class AdminExtension extends AbstractExtension
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

    public function getFunctions()
    {
        return [
            new TwigFunction('adminHeader', [new AdminHeaderFunction($this->handler), 'display'], ['is_safe' => ['html']]),
            new TwigFunction('adminBreadcrumbs', [new AdminBreadcrumbsFunction($this->handler), 'display'], ['is_safe' => ['html']]),
            new TwigFunction('adminUpdateCheck', [new AdminUpdateCheckFunction($this->handler), 'display'], ['is_safe' => ['html']]),
            new TwigFunction('adminSecurityAnalyzer', [new AdminSecurityAnalyzerFunction($this->handler), 'display'], ['is_safe' => ['html']]),
            new TwigFunction('adminMenu', [new AdminMenuFunction($this->handler, $this->permissionApi), 'display'], ['is_safe' => ['html']]),
            new TwigFunction('adminPanelMenu', [$this, 'adminPanelMenu'], ['is_safe' => ['html']]),
            new TwigFunction('adminFooter', [new AdminFooterFunction($this->handler), 'display'], ['is_safe' => ['html']])
        ];
    }

    public function adminPanelMenu(string $mode = 'modules', string $template = 'panel'): string
    {
        $adminMenu = new AdminMenuFunction($this->handler, $this->permissionApi);

        return $adminMenu->display($mode, $template);
    }
}
