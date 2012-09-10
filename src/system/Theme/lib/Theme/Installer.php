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

class Theme_Installer extends Zikula_AbstractInstaller
{
    /**
     * initialise the theme module
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance.
     * This function MUST exist in the pninit file for a module
     *
     * @return bool true on success, false otherwise
     */
    public function install()
    {
        // create the table
        if (!DBUtil::createTable('themes')) {
            return false;
        }

        // detect all themes on install
        ModUtil::loadApi('Theme', 'admin', true);
        ModUtil::apiFunc('Theme', 'admin', 'regenerate');

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
     * @param  string $oldVersion version number string to upgrade from
     * @return mixed  true on success, last valid version string or false if fails
     */
    public function upgrade($oldversion)
    {
        // update the table
        if (!DBUtil::changeTable('themes')) {
            return false;
        }

        switch ($oldversion) {
            case '3.1':
                $this->setVar('cssjscombine', false);
                $this->setVar('cssjscompress', false);
                $this->setVar('cssjsminify', false);
                $this->setVar('cssjscombine_lifetime', 3600);

            case '3.3':
            // convert pnRender modvars
                $pnrendervars = ModUtil::getVar('pnRender');
                foreach ($pnrendervars as $k => $v) {
                    $this->setVar('render_' . $k, $v);
                }
                // delete pnRender modvars
                ModUtil::delVar('pnRender');

                $modid = ModUtil::getIdFromName('pnRender');

                // check and update blocks
                $blocks = ModUtil::apiFunc('Blocks', 'user', 'getall', array('modid' => $modid));
                if (!empty($blocks)) {
                    $thememodid = ModUtil::getIdFromName('Theme');
                    foreach ($blocks as $block) {
                        $block->setBkey('render');
                        $block->setMid($thememodid);
                        $this->entityManager->flush();
                    }
                }

                // check and fix permissions
                $dbtable = DBUtil::getTables();
                $permscolumn = $dbtable['group_perms_column'];
                $permswhere = "WHERE $permscolumn[component] = 'pnRender:pnRenderblock:'";
                $perms = DBUtil::selectObjectArray('group_perms', $permswhere);
                if (!empty($perms)) {
                    foreach ($perms as $perm) {
                        $perm['component'] = 'Theme:Renderblock:';
                        DBUtil::updateObject($perm, 'group_perms', '', 'pid');
                    }
                }

                // Set Module pnRender 'Inactive'
                if (!ModUtil::apiFunc('Extensions', 'admin', 'setstate', array(
                'id' => $modid,
                'state' => ModUtil::STATE_INACTIVE))) {
                    return '3.3';
                }
                // Remove Module pnRender from Modulelist
                if (!ModUtil::apiFunc('Extensions', 'admin', 'remove', array(
                'id' => $modid))) {
                    return '3.3';
                }

            case '3.4':
                if (!DBUtil::changeTable('themes')) {
                    return '3.4';
                }
            case '3.4.1':
                if (!DBUtil::changeTable('themes')) {
                    return '3.4.1';
                }
                $this->setVar('enable_mobile_theme', false);
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
     * This function MUST exist in the pninit file for a module
     *
     * Since the theme module should never be deleted we'all always return false here
     * @return bool false
     */
    public function uninstall()
    {
        // drop the table
        if (!DBUtil::dropTable('Themes')) {
            return false;
        }

        // delete all module variables
        $this->delVar('Theme');

        // Deletion not allowed
        return false;
    }
}
