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

namespace Zikula\ThemeModule;

use Zikula\Core\AbstractExtensionInstaller;

/**
 * Installation and upgrade routines for the theme module
 */
class ThemeModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * Initialise the Admin module.
     *
     * @return boolean true if initialisation successful, false otherwise.
     */
    public function install()
    {
        // create the table
        try {
            $this->schemaTool->create(['Zikula\ThemeModule\Entity\ThemeEntity']);
        } catch (\Exception $e) {
            return false;
        }

        // detect all themes on install
        $this->container->get('zikula_theme_module.helper.bundle_sync_helper')->regenerate();

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
        $this->setVar('render_compile_check', true);
        $this->setVar('render_force_compile', false);
        $this->setVar('render_cache', false);
        $this->setVar('render_expose_template', false);
        $this->setVar('render_lifetime', 3600);

        // Initialisation successful
        return true;
    }

    /**
     * upgrade the module from an old version
     *
     * @param  string $oldversion version number string to upgrade from
     *
     * @return bool|string true on success, last valid version string or false if fails
     */
    public function upgrade($oldversion)
    {
        switch ($oldversion) {
            case '3.4.2':
                $this->delVar('enable_mobile_theme');
                // future upgrade
        }

        // Update successful
        return true;
    }

    /**
     * delete the Admin module
     *
     * @return bool true if deletion successful, false otherwise
     */
    public function uninstall()
    {
        // Deletion not allowed
        return false;
    }
}
