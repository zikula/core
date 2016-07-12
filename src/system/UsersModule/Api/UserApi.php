<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Api;

use DataUtil;
use ModUtil;
use SecurityUtil;

/**
 * @deprecated
 * The system-level and database-level functions for user-initiated actions for the Users module.
 */
class UserApi extends \Zikula_AbstractApi
{
    /**
     * @deprecated
     * Retrieve the account links for each user module.
     *
     * @return array An array of links for the user account page.
     */
    public function accountLinks()
    {
        // Get all user modules
        $mods = ModUtil::getAllMods();

        if ($mods == false) {
            return false;
        }

        $accountlinks = [];

        foreach ($mods as $mod) {
            // saves 17 system checks
            if ($mod['type'] == 3 && !in_array($mod['name'], ['ZikulaAdminModule', 'ZikulaCategoriesModule', 'ZikulaGroupsModule', 'ZikulaThemeModule', $this->name])) {
                continue;
            }

            $modpath = ($mod['type'] == ModUtil::TYPE_SYSTEM) ? 'system' : 'modules';

            $module = ModUtil::getModule($this->name);

            $ooAccountApiFileNs = DataUtil::formatForOS("{$modpath}/{$mod['directory']}/Api/AccountApi.php");
            $ooAccountApiFile = DataUtil::formatForOS("{$modpath}/{$mod['directory']}/Api/Account.php");
            $ooAccountApiFileOld = DataUtil::formatForOS("{$modpath}/{$mod['directory']}/lib/{$mod['directory']}/Api/Account.php");
            $legacyAccountApiFile = DataUtil::formatForOS("{$modpath}/{$mod['directory']}/pnaccountapi.php");
            if (null !== $module && class_exists($module->getNamespace().'\\Api\\AccountApi') || file_exists($ooAccountApiFileNs) || file_exists($ooAccountApiFile) || file_exists($ooAccountApiFileOld) || file_exists($legacyAccountApiFile)) {
                $items = ModUtil::apiFunc($mod['name'], 'account', 'getAll');
                if ($items) {
                    foreach ($items as $k => $item) {
                        // check every retured link for permissions
                        if (SecurityUtil::checkPermission('ZikulaUsersModule::', "$mod[name]::$item[title]", ACCESS_READ)) {
                            if (!isset($item['module'])) {
                                $item['module']  = $mod['name'];
                            }
                            // insert the indexed item
                            $accountlinks["$mod[name]{$k}"] = $item;
                        }
                    }
                }
            } else {
                $items = false;
            }
        }

        return $accountlinks;
    }
}
