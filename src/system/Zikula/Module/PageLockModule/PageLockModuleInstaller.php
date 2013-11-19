<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\PageLockModule;

use DoctrineHelper;

/**
 * Installation and upgrade routines for the pagelock module
 */
class PageLockModuleInstaller extends \Zikula_AbstractInstaller
{
    /**
     * initialize the module
     *
     * @return boolean True if initialisation successful, false otherwise.
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

    /**
     * upgrade the module from an old version
     *
     * @param string $oldversion version number string to upgrade from
     *
     * @return bool true as there are no upgrade routines currently
     */
    public function upgrade($oldversion)
    {
        return true;
    }

    /**
     * delete the Pagelock module
     *
     * @return bool true if deletion successful, false otherwise
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
