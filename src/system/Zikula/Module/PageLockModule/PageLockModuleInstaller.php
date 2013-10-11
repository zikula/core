<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\PageLockModule;

class PageLockModuleInstaller extends \Zikula_AbstractInstaller
{
    /**
     * initialize the module
     */
    public function install()
    {
        try {
            DoctrineHelper::createSchema($this->entityManager, array(
                'Zikula\Module\PageLockModule\Entity\PageLockEntity',
            ));
        } catch (\Exception $e) {
             return false;
        }

        return true;
    }

    public function upgrade($oldversion)
    {
        return true;
    }

    /**
     * delete the module
     */
    public function uninstall()
    {
        try {
            DoctrineHelper::createSchema($this->dropManager, array(
                'Zikula\Module\PageLockModule\Entity\PageLockEntity',
            ));
        } catch (\Exception $e) {
             return false;
        }

        return true;
    }
}
