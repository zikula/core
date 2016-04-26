<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Api;

use SecurityUtil;

/**
 * Administrative API's for the categories module
 */
class AdminApi extends \Zikula_AbstractApi
{
    /**
     * Get available admin panel links.
     *
     * @return array array of admin links.
     */
    public function getLinks()
    {
        $links = [];

        if (SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_READ)) {
            $links[] = [
                'url' => $this->get('router')->generate('zikulacategoriesmodule_admin_view'),
                'text' => $this->__('Categories list'),
                'icon' => 'list'
            ];
        }
        if (SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_ADD)) {
            $links[] = [
                'url' => $this->get('router')->generate('zikulacategoriesmodule_admin_newcat'),
                'text' => $this->__('Create new category'),
                'icon' => 'plus'
            ];
        }
        if (SecurityUtil::checkPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            $links[] = [
                'url' => $this->get('router')->generate('zikulacategoriesmodule_admin_editregistry'),
                'text' => $this->__('Category registry'),
                'icon' => 'archive'
            ];
            $links[] = [
                'url' => $this->get('router')->generate('zikulacategoriesmodule_admin_config'),
                'text' => $this->__('Rebuild paths'),
                'icon' => 'refresh'
            ];
            $links[] = [
                'url' => $this->get('router')->generate('zikulacategoriesmodule_admin_preferences'),
                'text' => $this->__('Settings'),
                'icon' => 'wrench'
            ];
        }

        return $links;
    }
}
