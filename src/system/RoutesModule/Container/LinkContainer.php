<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Zikula\RoutesModule\Container;

use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\RoutesModule\Container\Base\AbstractLinkContainer;

/**
 * This is the link container service implementation class.
 */
class LinkContainer extends AbstractLinkContainer
{
    public function getLinks(string $type = LinkContainerInterface::TYPE_ADMIN): array
    {
        $links = [];

        if (LinkContainerInterface::TYPE_ADMIN !== $type) {
            return $links;
        }

        if (!$this->permissionHelper->hasComponentPermission('route', ACCESS_ADMIN)) {
            return $links;
        }

        $links[] = [
            'url' => $this->router->generate('zikularoutesmodule_route_adminview'),
            'text' => $this->trans('Routes'),
            'title' => $this->trans('Route list')
        ];
        $links[] = [
            'url' => $this->router->generate('zikularoutesmodule_update_reload'),
            'text' => $this->trans('Reload routes'),
            'title' => $this->trans('Reload routes')
        ];
        $links[] = [
            'url' => $this->router->generate('zikularoutesmodule_update_renew'),
            'text' => $this->trans('Reload multilingual routing settings'),
            'title' => $this->trans('Reload multilingual routing settings')
        ];
        $links[] = [
            'url' => $this->router->generate('zikularoutesmodule_update_dumpjsroutes'),
            'text' => $this->trans('Dump exposed js routes to file'),
            'title' => $this->trans('Dump exposed js routes to file')
        ];
        if ($this->permissionHelper->hasPermission(ACCESS_ADMIN)) {
            $links[] = [
                'url' => $this->router->generate('zikularoutesmodule_config_config'),
                'text' => $this->trans('Configuration'),
                'title' => $this->trans('Manage settings for this application'),
                'icon' => 'wrench'
            ];
        }

        return $links;
    }
}
