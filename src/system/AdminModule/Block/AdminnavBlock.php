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

use ModUtil;
use Zikula\BlocksModule\AbstractBlockHandler;

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

        // Call the modules API to get the items
        $items = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getall');

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
            foreach ($adminModules as $adminModule) {
                // Get all modules in the category
                $catid = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getmodcategory',
                                            ['mid' => ModUtil::getIdFromName($adminModule['name'])]);

                if ($catid == $item['cid'] || (false == $catid && $item['cid'] == $defaultCategory)) {
                    $moduleInfo = ModUtil::getInfoFromName($adminModule['name']);
                    $adminLinks[] = [
                        'menuTextUrl' => ModUtil::url($moduleInfo['name'], 'admin'),
                        'menuTextTitle' => $moduleInfo['displayname']
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
