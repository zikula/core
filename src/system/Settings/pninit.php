<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2002, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Settings
 */

/**
 * initialise the settings module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 * @return bool true if successful, false otherwise
 */
function settings_init()
{
    // Set up an initial value for a module variable.  Note that all module
    // variables should be initialised with some value in this way rather
    // than just left blank, this helps the user-side code and means that
    // there doesn't need to be a check to see if the variable is set in
    // the rest of the code as it always will be
    pnConfigSetVar('debug', '0');
    pnConfigSetVar('sitename', __('Site name'));
    pnConfigSetVar('slogan', __('Site description'));
    pnConfigSetVar('metakeywords', __('zikula, community, portal, portal web, open source, gpl, web site, website, weblog, blog, content management, content management system, web content management, web content management system, enterprise web content management, cms, application framework'));
    pnConfigSetVar('startdate', date('m/Y', time()));
    pnConfigSetVar('adminmail', 'me@example.com');
    pnConfigSetVar('Default_Theme', 'andreas08');
    pnConfigSetVar('anonymous', __('Guest'));
    pnConfigSetVar('timezone_offset', '0');
    pnConfigSetVar('timezone_server', '0');
    pnConfigSetVar('funtext', '1');
    pnConfigSetVar('reportlevel', '0');
    pnConfigSetVar('startpage', 'blank');
    pnConfigSetVar('Version_Num', Z_VERSION_NUM);
    pnConfigSetVar('Version_ID', Z_VERSION_ID);
    pnConfigSetVar('Version_Sub', Z_VERSION_SUB);
    pnConfigSetVar('debug_sql', '0');
    pnConfigSetVar('multilingual', '1');
    pnConfigSetVar('useflags', '0');
    pnConfigSetVar('theme_change', '0');
    pnConfigSetVar('UseCompression', '0');
    pnConfigSetVar('errordisplay', 1);
    pnConfigSetVar('errorlog', 0);
    pnConfigSetVar('errorlogtype', 0);
    pnConfigSetVar('errormailto', 'me@example.com');
    pnConfigSetVar('siteoff', 0);
    pnConfigSetVar('siteoffreason', '');
    pnConfigSetVar('starttype', '');
    pnConfigSetVar('startfunc', '');
    pnConfigSetVar('startargs', '');
    pnConfigSetVar('entrypoint', 'index.php');
    pnConfigsetVar('language_detect', 0);
    pnConfigSetVar('shorturls', false);
    pnConfigSetVar('shorturlstype', '0');
    pnConfigSetVar('shorturlsext', 'html');
    pnConfigSetVar('shorturlsseparator', '-');
    pnConfigSetVar('shorturlsstripentrypoint', false);
    pnConfigSetVar('shorturlsdefaultmodule', '');
    $groupModules = _settings_getDefaultGroupModules();
    pnConfigSetVar('profilemodule', $groupModules['profile']);
    pnConfigSetVar('messagemodule', $groupModules['message']);
    pnConfigSetVar('languageurl', 0);
    pnConfigSetVar('ajaxtimeout', 5000);
    //! this is a comma-separated list of special characters to search for in permalinks
    pnConfigSetVar('permasearch',  __('À,Á,Â,Ã,Å,à,á,â,ã,å,Ò,Ó,Ô,Õ,Ø,ò,ó,ô,õ,ø,È,É,Ê,Ë,è,é,ê,ë,Ç,ç,Ì,Í,Î,Ï,ì,í,î,ï,Ù,Ú,Û,ù,ú,û,ÿ,Ñ,ñ,ß,ä,Ä,ö,Ö,ü,Ü'));
    //! this is a comma-separated list of special characters to replace in permalinks
    pnConfigSetVar('permareplace', __('A,A,A,A,A,a,a,a,a,a,O,O,O,O,O,o,o,o,o,o,E,E,E,E,e,e,e,e,C,c,I,I,I,I,i,i,i,i,U,U,U,u,u,u,y,N,n,s,a,A,o,O,u,U'));

    pnConfigSetVar('language',ZLanguage::getLanguageCodeLegacy());
    pnConfigSetVar('locale', ZLanguage::getLocale());
    pnConfigSetVar('language_i18n', ZLanguage::getlanguageCode());
    pnConfigSetVar('language_bc', 1);

    // Initialisation successful
    return true;
}

/**
 * upgrade the settings module from an old version
 *
 * This function must consider all the released versions of the module!
 * If the upgrade fails at some point, it returns the last upgraded version.
 *
 * @param        string   $oldVersion   version number string to upgrade from
 * @return       mixed    true on success, last valid version string or false if fails
 */
function settings_upgrade($oldversion)
{
    // always ensure that the version info is upgraded
    pnConfigSetVar('Version_Num', Z_VERSION_NUM);
    pnConfigSetVar('Version_ID', Z_VERSION_ID);
    pnConfigSetVar('Version_Sub', Z_VERSION_SUB);

    // Upgrade dependent on old version number
    switch ($oldversion)
    {
        case '2.5':
            pnConfigDelVar('jsquicktags');
            pnConfigDelVar('backend_title');
            pnConfigDelVar('refereronprint');
            pnConfigDelVar('storyorder');
            pnConfigDelVar('backend_language');
            pnConfigDelVar('site_logo');

        case '2.6':
            pnConfigSetVar('updatelastchecked', 0);
            pnConfigSetVar('updatefrequency', 7);
            pnConfigSetVar('updateversion', Z_VERSION_NUM);
            pnConfigSetVar('updatecheck', true);

        case '2.7':
            pnConfigSetVar('language_i18n', 'en');
            pnConfigSetVar('language_bc', 1);
            pnConfigSetVar('languageurl', 0);
            pnConfigSetVar('ajaxtimeout', 5000);
            //! this is a comma-separated list of special characters to search for in permalinks
            pnConfigSetVar('permasearch',  __('À,Á,Â,Ã,Å,à,á,â,ã,å,Ò,Ó,Ô,Õ,Ø,ò,ó,ô,õ,ø,È,É,Ê,Ë,è,é,ê,ë,Ç,ç,Ì,Í,Î,Ï,ì,í,î,ï,Ù,Ú,Û,ù,ú,û,ÿ,Ñ,ñ,ß,ä,Ä,ö,Ö,ü,Ü'));
            //! this is a comma-separated list of special characters to replace in permalinks
            pnConfigSetVar('permareplace', __('A,A,A,A,A,a,a,a,a,a,O,O,O,O,O,o,o,o,o,o,E,E,E,E,e,e,e,e,C,c,I,I,I,I,i,i,i,i,U,U,U,u,u,u,y,N,n,s,a,A,o,O,u,U'));

        case '2.8':
            pnConfigDelVar('dyn_keywords');
        case '2.9':
        case '2.9.1':
            pnConfigDelVar('timezone_info');
        case '2.9.2':
            // future upgrade routines
    }

    // Update successful
    return true;
}

/**
 * delete the settings module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 * @return bool true if successful, false otherwise
 */
function settings_delete()
{
    // Deletion fail - we dont want users disabling this module!
    return false;
}

/**
 * utility function to retrieve default values for module group vars
 *
 * @return array with determined module names
 */
function _settings_getDefaultGroupModules()
{
    $groupModules = array();

    $groupModules['profile'] = '';
    if (ModUtil::available('Profile')) {
        $groupModules['profile'] = 'Profile';

    } elseif (ModUtil::available('MyProfile')) {
        $groupModules['profile'] = 'MyProfile';
    }

    $groupModules['message'] = '';
    if (ModUtil::available('InterCom')) {
        $groupModules['message'] = 'InterCom';
    }

    return $groupModules;
}
