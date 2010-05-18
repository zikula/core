<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage SecurityCenter
*/

/**
 * initialise the SecurityCenter module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 * @return bool true on success, false otherwise
 */
function securitycenter_init()
{
    // create table
    if (!DBUtil::createTable('sc_anticracker')) {
        return false;
    }

    // create table
    if (!DBUtil::createTable('sc_logevent')) {
        return false;
    }

    // create ids intrusions table
    if (!DBUtil::createTable('sc_intrusion')) {
        return false;
    }

    // Set up an initial value for a module variable.
    ModUtil::setVar('SecurityCenter', 'itemsperpage', 10);

    // We use config vars for the rest of the configuration as config vars
    // are available earlier in the PN initialisation process
    pnConfigSetVar('enableanticracker', 1);
    pnConfigSetVar('emailhackattempt', 1);
    pnConfigSetVar('loghackattempttodb', 1);
    pnConfigSetVar('onlysendsummarybyemail', 1);
    pnConfigSetVar('updatecheck', 1);
    pnConfigSetVar('updatefrequency', 7);
    pnConfigSetVar('updatelastchecked', 0);
    pnConfigSetVar('updateversion', System::VERSION_NUM);
    pnConfigSetVar('keyexpiry', 0);
    pnConfigSetVar('sessionauthkeyua', false);
    pnConfigSetVar('secure_domain', '');
    pnConfigSetVar('signcookies', 1);
    pnConfigSetVar('signingkey', sha1(rand(0, time())));
    pnConfigSetVar('seclevel', 'Medium');
    pnConfigSetVar('secmeddays', 7);
    pnConfigSetVar('secinactivemins', 20);
    pnConfigSetVar('sessionstoretofile', 0);
    pnConfigSetVar('sessionsavepath', '');
    pnConfigSetVar('gc_probability', 100);
    pnConfigSetVar('anonymoussessions', 1);
    pnConfigSetVar('sessionrandregenerate', true);
    pnConfigSetVar('sessionregenerate', true);
    pnConfigSetVar('sessionregeneratefreq', 10);
    pnConfigSetVar('sessionipcheck', 0);
    pnConfigSetVar('sessionname', 'ZSID');

    pnConfigSetVar('filtergetvars', 1);
    pnConfigSetVar('filterpostvars', 1);
    pnConfigSetVar('filtercookievars', 1);
    pnConfigSetVar('outputfilter', 1);

    // Location of HTML Purifier
    pnConfigSetVar('htmlpurifierlocation', 'system/SecurityCenter/pnincludes/htmlpurifier/');

    // HTML Purifier cache dir
    $purifierCacheDir = CacheUtil::getLocalDir() . '/purifierCache';
    if (!file_exists($purifierCacheDir)) {
        CacheUtil::clearLocalDir('purifierCache');
    }

    // create vars for phpids usage
    pnConfigSetVar('useids', 0);
    pnConfigSetVar('idsfilter', 'xml');               // filter type
    pnConfigSetVar('idsimpactthresholdone', 1);       // db logging
    pnConfigSetVar('idsimpactthresholdtwo', 10);      // mail admin
    pnConfigSetVar('idsimpactthresholdthree', 25);    // block request
    pnConfigSetVar('idsimpactthresholdfour', 75);     // kick user, destroy session
    pnConfigSetVar('idsimpactmode', 1);               // per request per default

    // now lets set the default mail message contents
    // file is read from pnincludes directory
    $summarycontent = implode('', file(getcwd() . '/system/SecurityCenter/pnincludes/summary.txt'));
    pnConfigSetVar('summarycontent', $summarycontent);
    $fullcontent = implode('', file(getcwd() . '/system/SecurityCenter/pnincludes/full.txt'));
    pnConfigSetVar('fullcontent', $fullcontent);

    // cci vars, see pndocs/ccisecuritystrings.txt
    pnConfigSetVar('usehtaccessbans', 0);
    pnConfigSetVar('extrapostprotection', 0);
    pnConfigSetVar('extragetprotection', 0);
    pnConfigSetVar('checkmultipost', 0);
    pnConfigSetVar('maxmultipost', 4);
    pnConfigSetVar('cpuloadmonitor', 0);
    pnConfigSetVar('cpumaxload', 10.0);
    pnConfigSetVar('ccisessionpath', '');
    pnConfigSetVar('htaccessfilelocation', '.htaccess');
    pnConfigSetVar('nocookiebanthreshold', 10);
    pnConfigSetVar('nocookiewarningthreshold', 2);
    pnConfigSetVar('fastaccessbanthreshold', 40);
    pnConfigSetVar('fastaccesswarnthreshold', 10);
    pnConfigSetVar('javababble', 0);
    pnConfigSetVar('javaencrypt', 0);
    pnConfigSetVar('preservehead', 0);
    pnConfigSetVar('filterarrays', 1);
    pnConfigSetVar('htmlentities', '1');

    // default values for AllowableHTML
    $defhtml = array('!--' => 2,
                     'a' => 2,
                     'abbr' => 0,
                     'acronym' => 0,
                     'address' => 0,
                     'applet' => 0,
                     'area' => 0,
                     'b' => 2,
                     'base' => 0,
                     'basefont' => 0,
                     'bdo' => 0,
                     'big' => 0,
                     'blockquote' => 2,
                     'br' => 2,
                     'button' => 0,
                     'caption' => 0,
                     'center' => 2,
                     'cite' => 0,
                     'code' => 0,
                     'col' => 0,
                     'colgroup' => 0,
                     'del' => 0,
                     'dfn' => 0,
                     'dir' => 0,
                     'div' => 2,
                     'dl' => 1,
                     'dd' => 1,
                     'dt' => 1,
                     'em' => 2,
                     'embed' => 0,
                     'fieldset' => 0,
                     'font' => 0,
                     'form' => 0,
                     'h1' => 1,
                     'h2' => 1,
                     'h3' => 1,
                     'h4' => 1,
                     'h5' => 1,
                     'h6' => 1,
                     'hr' => 2,
                     'i' => 2,
                     'iframe' => 0,
                     'img' => 0,
                     'input' => 0,
                     'ins' => 0,
                     'kbd' => 0,
                     'label' => 0,
                     'legend' => 0,
                     'li' => 2,
                     'map' => 0,
                     'marquee' => 0,
                     'menu' => 0,
                     'nobr' => 0,
                     'object' => 0,
                     'ol' => 2,
                     'optgroup' => 0,
                     'option' => 0,
                     'p' => 2,
                     'param' => 0,
                     'pre' => 2,
                     'q' => 0,
                     's' => 0,
                     'samp' => 0,
                     'script' => 0,
                     'select' => 0,
                     'small' => 0,
                     'span' => 0,
                     'strike' => 0,
                     'strong' => 2,
                     'sub' => 0,
                     'sup' => 0,
                     'table' => 2,
                     'tbody' => 0,
                     'td' => 2,
                     'textarea' => 0,
                     'tfoot' => 0,
                     'th' => 2,
                     'thead' => 0,
                     'tr' => 2,
                     'tt' => 2,
                     'u' => 0,
                     'ul' => 2,
                     'var' => 0);
    pnConfigSetVar('AllowableHTML', $defhtml);

    // Initialisation successful
    return true;
}

/**
 * upgrade the SecurityCenter module from an old version
 *
 * This function must consider all the released versions of the module!
 * If the upgrade fails at some point, it returns the last upgraded version.
 *
 * @param        string   $oldVersion   version number string to upgrade from
 * @return       mixed    true on success, last valid version string or false if fails
 */
function securitycenter_upgrade($oldversion)
{
    if (!DBUtil::changeTable('sc_anticracker')) {
        return false;
    }

    if (!DBUtil::changeTable('sc_logevent')) {
        return false;
    }

    switch ($oldversion)
    {

        case '1.3':
            // create cache directory for HTML Purifier
            $purifierCacheDir = CacheUtil::getLocalDir() . '/purifierCache';
            if (!file_exists($purifierCacheDir)) {
                CacheUtil::clearLocalDir('purifierCache');
            }

            // create ids intrusions table
            if (!DBUtil::createTable('sc_intrusion')) {
                return false;
            }

            // create vars for phpids usage
            pnConfigSetVar('useids', 0);
            pnConfigSetVar('idsfilter', 'xml');               // filter type
            pnConfigSetVar('idsimpactthresholdone', 1);       // db logging
            pnConfigSetVar('idsimpactthresholdtwo', 10);      // mail admin
            pnConfigSetVar('idsimpactthresholdthree', 25);    // block request
            pnConfigSetVar('idsimpactthresholdfour', 75);     // kick user, destroy session
            pnConfigSetVar('idsimpactmode', 1);               // per request per default
            // fall through

        case '1.4':
            // Location of HTML Purifier
            pnConfigSetVar('htmlpurifierlocation', 'system/SecurityCenter/pnincludes/htmlpurifier/');
            // fall through

        case '1.6':
            // future upgrade routines
    }

    // Update successful
    return true;
}

/**
 * delete the SecurityCenter module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 * @return bool true on success, false otherwise
 */
function securitycenter_delete()
{
    // Deletion fail - we dont want users disabling this module!
    return false;
}

/**
 * This function deletes obsolete config vars.
 * This will become more important soon (#825)
 */
function _securitycenter_deleteObsoleteConfigVars()
{
    $obsoleteVars = array('zipcompress', 'compresslevel');
    foreach($obsoleteVars as $obVar) {
        pnConfigDelVar($obVar);
    }
}
