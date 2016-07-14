<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PageLockModule;

use Zikula\Core\AbstractExtensionInstaller;

/**
 * Installation and upgrade routines for the pagelock module.
 */
class PageLockModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * Initialise the module.
     *
     * @return boolean True if initialisation successful, false otherwise.
     */
    public function install()
    {
        try {
            $this->schemaTool->create([
                'Zikula\PageLockModule\Entity\PageLockEntity',
            ]);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * upgrade the module from an old version
     *
     * @param string $oldVersion version number string to upgrade from
     *
     * @return bool true as there are no upgrade routines currently
     */
    public function upgrade($oldVersion)
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
            $this->schemaTool->drop([
                'Zikula\PageLockModule\Entity\PageLockEntity'
            ]);
        } catch (\PDOException $e) {
            $this->addFlash('error', $e->getMessage());

            return false;
        }

        // Delete any module variables.
        $this->delVars();

        // Deletion successful.
        return true;
    }
}
