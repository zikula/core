<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Block;

use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;

/**
 * Administrative navigation block
 */
class AdminnavBlock extends AbstractBlockHandler
{
    /**
     * display block
     *
     * @param array $properties
     *
     * @return string html of the rendered blcok
     */
    public function display(array $properties)
    {
        // Security check
        if (!$this->hasPermission('ZikulaAdminModule:adminnavblock', $properties['title'] . '::' . $properties['bid'], ACCESS_ADMIN)) {
            return;
        }

        $adminCategoryRepository = $this->get('zikula_admin_module.admin_category_repository');

        // Get all categories
        $items = $adminCategoryRepository->findBy([], ['sortorder' => 'ASC']);

        // Check for no items returned
        if (empty($items)) {
            return '';
        }

        // get admin capable modules
        $adminModules = $this->get('zikula_extensions_module.api.capability')->getExtensionsCapableOf('admin');
        $defaultCategory = $this->get('zikula_extensions_module.api.variable')->get('ZikulaAdminModule', 'defaultcategory');

        // Display each item, permissions permitting
        $adminCategories = [];
        foreach ($items as $item) {
            if (!$this->hasPermission('ZikulaAdminModule::', $item['name'] . '::' . $item['cid'], ACCESS_READ)) {
                continue;
            }

            $adminLinks = [];
            /** @var ExtensionEntity[] $adminModules */
            foreach ($adminModules as $adminModule) {
                $category = $adminCategoryRepository->getModuleCategory($adminModule->getId());
                if ($category['cid'] == $item['cid'] || (false === $category['cid'] && $item['cid'] == $defaultCategory)) {
                    $menuText = $adminModule->getDisplayname();
                    // url
                    try {
                        $menuTextUrl = isset($adminModule['capabilities']['admin']['route']) ? $this->get('router')->generate($adminModule['capabilities']['admin']['route']) : $adminModule['capabilities']['admin']['url'];
                    } catch (RouteNotFoundException $routeNotFoundException) {
                        $menuTextUrl = 'javascript:void(0)';
                        $menuText .= ' (<i class="fa fa-warning"></i> ' . $this->__('invalid route') . ')';
                    }
                    $adminLinks[] = [
                        'menuTextUrl' => $menuTextUrl,
                        'menuTextTitle' => $menuText
                    ];
                }
            }
            $adminCategories[] = [
                'url' => $this->get('router')->generate('zikulaadminmodule_admin_adminpanel', ['cid' => $item['cid']]),
                'title' => $item['name'],
                'modules' => $adminLinks
            ];
        }

        $templateParameters = [
            'adminCategories' => $adminCategories
        ];

        return $this->renderView('@ZikulaAdminModule/Block/adminNav.html.twig', $templateParameters);
    }
}
