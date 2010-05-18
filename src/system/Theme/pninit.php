<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2004, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Theme
 */

/**
 * initialise the theme module
 *
 * This function is only ever called once during the lifetime of a particular
 * module instance.
 * This function MUST exist in the pninit file for a module
 *
 * @return       bool       true on success, false otherwise
 */
function theme_init()
{
    // create the table
    if (!DBUtil::createTable('themes')) {
        return false;
    }

    // detect all themes on install
    pnModAPILoad('Theme', 'admin', true);
    pnModAPIFunc('Theme', 'admin', 'regenerate');

    // define defaults for module vars
    ModUtil::setVar('Theme', 'modulesnocache', '');
    ModUtil::setVar('Theme', 'enablecache', false);
    ModUtil::setVar('Theme', 'compile_check', true);
    ModUtil::setVar('Theme', 'cache_lifetime', 3600);
    ModUtil::setVar('Theme', 'force_compile', false);
    ModUtil::setVar('Theme', 'trimwhitespace', false);
    ModUtil::setVar('Theme', 'maxsizeforlinks', 30);
    ModUtil::setVar('Theme', 'itemsperpage', 25);

    ModUtil::setVar('Theme', 'cssjscombine', false);
    ModUtil::setVar('Theme', 'cssjscompress', false);
    ModUtil::setVar('Theme', 'cssjsminify', false);
    ModUtil::setVar('Theme', 'cssjscombine_lifetime', 3600);

    // Renderer
    ModUtil::setVar('Theme', 'render_compile_check',  true);
    ModUtil::setVar('Theme', 'render_force_compile',  true);
    ModUtil::setVar('Theme', 'render_cache',          false);
    ModUtil::setVar('Theme', 'render_expose_template',false);
    ModUtil::setVar('Theme', 'render_lifetime',       3600);

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
function theme_upgrade($oldversion)
{
    // update the table
    if (!DBUtil::changeTable('themes')) {
        return false;
    }

    switch ($oldversion)
    {
        case '3.1':
            ModUtil::setVar('Theme', 'cssjscombine', false);
            ModUtil::setVar('Theme', 'cssjscompress', false);
            ModUtil::setVar('Theme', 'cssjsminify', false);
            ModUtil::setVar('Theme', 'cssjscombine_lifetime', 3600);

        case '3.3':
            // convert pnRender modvars
            $pnrendervars = ModUtil::getVar('pnRender');
            foreach ($pnrendervars as $k => $v) {
                ModUtil::setVar('Theme', 'render_' . $k, $v);
            }
            // delete pnRender modvars
            pnModDelVar('pnRender');

            $modid = ModUtil::getIdFromName('pnRender');

            // check and update blocks
            $blocks = pnModAPIFunc('Blocks', 'user', 'getall', array('modid' => $modid));
            if (!empty($blocks)) {
                $thememodid = ModUtil::getIdFromName('Theme');
                foreach ($blocks as $block) {
                    $block['bkey'] = 'render';
                    $block['mid'] = $thememodid;
                    DBUtil::updateObject($block, 'blocks', '', 'bid');
                }
            }

            // check and fix permissions
            $pntable = pnDBGetTables();
            $permscolumn = $pntable['group_perms_column'];
            $permswhere = "WHERE $permscolumn[component] = 'pnRender:pnRenderblock:'";
            $perms = DBUtil::selectObjectArray('group_perms', $permswhere);
            if (!empty($perms)) {
                foreach ($perms as $perm) {
                    $perm['component'] = 'Theme:Renderblock:';
                    DBUtil::updateObject($perm, 'group_perms', '', 'pid');
                }

            }

            // Set Module pnRender 'Inactive'
            if (!pnModAPIFunc('Modules', 'admin', 'setstate', array(
                'id' => $modid,
                'state' => PNMODULE_STATE_INACTIVE))) {
                return '3.3';
            }
            // Remove Module pnRender from Modulelist
            if (!pnModAPIFunc('Modules', 'admin', 'remove', array(
                'id' => $modid))) {
                return '3.3';
            }

        case '3.4':
            if (!DBUtil::changeTable('Themes')) {
                return '3.4';
            }

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
 * @return       bool       false
 */
function theme_delete()
{
    // drop the table
    if (!DBUtil::dropTable('Themes')) {
        return false;
    }

    // delete all module variables
    pnModDelVar('Theme');

    // Deletion not allowed
    return false;
}
