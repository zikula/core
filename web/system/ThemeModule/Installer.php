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

namespace ThemeModule;

use DoctrineHelper, ModUtil;

class Installer extends \Zikula_AbstractInstaller
{
    /**
     * initialise the theme module
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance.
     *
     * @return       bool       true on success, false otherwise
     */
    public function install()
    {
        // create the table
        try {
            DoctrineHelper::createSchema($this->entityManager, array('ThemeModule\Entity\Theme'));
        } catch (\Exception $e) {
            return false;
        }

        // detect all themes on install
        ModUtil::loadApi('Theme', 'admin', true);
        ModUtil::apiFunc('ThemeModule', 'admin', 'regenerate');

        // define defaults for module vars
        $this->setVar('modulesnocache', '');
        $this->setVar('enablecache', false);
        $this->setVar('compile_check', true);
        $this->setVar('cache_lifetime', 1800);
        $this->setVar('cache_lifetime_mods', 1800);
        $this->setVar('force_compile', false);
        $this->setVar('trimwhitespace', false);
        $this->setVar('maxsizeforlinks', 30);
        $this->setVar('itemsperpage', 25);

        $this->setVar('cssjscombine', false);
        $this->setVar('cssjscompress', false);
        $this->setVar('cssjsminify', false);
        $this->setVar('cssjscombine_lifetime', 3600);

        // View
        $this->setVar('render_compile_check',  true);
        $this->setVar('render_force_compile',  false);
        $this->setVar('render_cache',          true);
        $this->setVar('render_expose_template',false);
        $this->setVar('render_lifetime',       3600);

        // Initialisation successful
        return true;
    }

    /**
     * upgrade the theme module from an old version
     *
     * This function must consider all the released versions of the module!
     * If the upgrade fails at some point, it returns the last upgraded version.
     *
     * @param        string   $oldVersion   version number string to upgrade from
     * @return       mixed    true on success, last valid version string or false if fails
     */
    public function upgrade($oldversion)
    {
        switch ($oldversion) {
            case '3.4.2':
                // future upgrade
        }

        // Update successful
        return true;
    }

    /**
     * delete the theme module
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance
     *
     * Since the theme module should never be deleted we'all always return false here
     * @return       bool       false
     */
    public function uninstall()
    {
        // Deletion not allowed
        return false;
    }
}