<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class SecurityCenter_Installer extends Zikula_Installer
{
    /**
     * initialise the SecurityCenter module
     * This function is only ever called once during the lifetime of a particular
     * module instance
     * @return bool true on success, false otherwise
     */
    public function install()
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
        $this->setVar('itemsperpage', 10);

        // We use config vars for the rest of the configuration as config vars
        // are available earlier in the PN initialisation process
        System::setVar('enableanticracker', 1);
        System::setVar('emailhackattempt', 1);
        System::setVar('loghackattempttodb', 1);
        System::setVar('onlysendsummarybyemail', 1);
        System::setVar('updatecheck', 1);
        System::setVar('updatefrequency', 7);
        System::setVar('updatelastchecked', 0);
        System::setVar('updateversion', System::VERSION_NUM);
        System::setVar('keyexpiry', 0);
        System::setVar('sessionauthkeyua', false);
        System::setVar('secure_domain', '');
        System::setVar('signcookies', 1);
        System::setVar('signingkey', sha1(rand(0, time())));
        System::setVar('seclevel', 'Medium');
        System::setVar('secmeddays', 7);
        System::setVar('secinactivemins', 20);
        System::setVar('sessionstoretofile', 0);
        System::setVar('sessionsavepath', '');
        System::setVar('gc_probability', 100);
        System::setVar('anonymoussessions', 1);
        System::setVar('sessionrandregenerate', true);
        System::setVar('sessionregenerate', true);
        System::setVar('sessionregeneratefreq', 10);
        System::setVar('sessionipcheck', 0);
        System::setVar('sessionname', 'ZSID');

        System::setVar('filtergetvars', 1);
        System::setVar('filterpostvars', 1);
        System::setVar('filtercookievars', 1);
        System::setVar('outputfilter', 1);

        // Location of HTML Purifier
        System::setVar('htmlpurifierlocation', 'system/SecurityCenter/lib/vendor/htmlpurifier/');

        // HTML Purifier cache dir
        $purifierCacheDir = CacheUtil::getLocalDir() . '/purifierCache';
        if (!file_exists($purifierCacheDir)) {
            CacheUtil::clearLocalDir('purifierCache');
        }

        // HTML Purifier default settings
        $purifierDefaultConfig = SecurityCenter_Api_User::getpurifierconfig(array('forcedefault' => true));
        $this->setVar('htmlpurifierConfig', serialize($purifierDefaultConfig));

        // create vars for phpids usage
        System::setVar('useids', 0);
        System::setVar('idssoftblock', 1);                // do not block requests, but warn for debugging
        System::setVar('idsfilter', 'xml');               // filter type
        System::setVar('idsimpactthresholdone', 1);       // db logging
        System::setVar('idsimpactthresholdtwo', 10);      // mail admin
        System::setVar('idsimpactthresholdthree', 25);    // block request
        System::setVar('idsimpactthresholdfour', 75);     // kick user, destroy session
        System::setVar('idsimpactmode', 1);               // per request per default
        System::setVar('idshtmlfields', array('POST.__wysiwyg'));
        System::setVar('idsjsonfields', array('POST.__jsondata'));
        System::setVar('idsexceptions', array(  'GET.__utmz',
                'GET.__utmc',
                'REQUEST.linksorder', 'POST.linksorder',
                'REQUEST.fullcontent', 'POST.fullcontent',
                'REQUEST.summarycontent', 'POST.summarycontent',
                'REQUEST.filter.page', 'POST.filter.page',
                'REQUEST.filter.value', 'POST.filter.value'));

        // now lets set the default mail message contents
        // file is read from includes directory
        $summarycontent = implode('', file(getcwd() . '/system/SecurityCenter/lib/vendor/summary.txt'));
        System::setVar('summarycontent', $summarycontent);
        $fullcontent = implode('', file(getcwd() . '/system/SecurityCenter/lib/vendor/full.txt'));
        System::setVar('fullcontent', $fullcontent);

        // cci vars, see pndocs/ccisecuritystrings.txt
        System::setVar('usehtaccessbans', 0);
        System::setVar('extrapostprotection', 0);
        System::setVar('extragetprotection', 0);
        System::setVar('checkmultipost', 0);
        System::setVar('maxmultipost', 4);
        System::setVar('cpuloadmonitor', 0);
        System::setVar('cpumaxload', 10.0);
        System::setVar('ccisessionpath', '');
        System::setVar('htaccessfilelocation', '.htaccess');
        System::setVar('nocookiebanthreshold', 10);
        System::setVar('nocookiewarningthreshold', 2);
        System::setVar('fastaccessbanthreshold', 40);
        System::setVar('fastaccesswarnthreshold', 10);
        System::setVar('javababble', 0);
        System::setVar('javaencrypt', 0);
        System::setVar('preservehead', 0);
        System::setVar('filterarrays', 1);
        System::setVar('htmlentities', '1');

        // default values for AllowableHTML
        $defhtml = array('!--' => 2,
                'a' => 2,
                'abbr' => 0,
                'acronym' => 0,
                'address' => 0,
                'applet' => 0,
                'area' => 0,
                'article' => 0,
                'aside' => 0,
                'audio' => 0,
                'b' => 2,
                'base' => 0,
                'basefont' => 0,
                'bdo' => 0,
                'big' => 0,
                'blockquote' => 2,
                'br' => 2,
                'button' => 0,
                'canvas' => 0,
                'caption' => 0,
                'center' => 2,
                'cite' => 0,
                'code' => 0,
                'col' => 0,
                'colgroup' => 0,
                'command' => 0,
                'datalist' => 0,
                'dd' => 1,
                'del' => 0,
                'details' => 0,
                'dfn' => 0,
                'dir' => 0,
                'div' => 2,
                'dl' => 1,
                'dt' => 1,
                'em' => 2,
                'embed' => 0,
                'fieldset' => 0,
                'figcaption' => 0,
                'figure' => 0,
                'font' => 0,
                'form' => 0,
                'h1' => 1,
                'h2' => 1,
                'h3' => 1,
                'h4' => 1,
                'h5' => 1,
                'h6' => 1,
                'hgroup' => 0,
                'hr' => 2,
                'i' => 2,
                'iframe' => 0,
                'img' => 0,
                'input' => 0,
                'ins' => 0,
                'keygen' => 0,
                'kbd' => 0,
                'label' => 0,
                'legend' => 0,
                'li' => 2,
                'map' => 0,
                'mark' => 0,
                'menu' => 0,
                'marquee' => 0,
                'meter' => 0,
                'nav' => 0,
                'nobr' => 0,
                'object' => 0,
                'ol' => 2,
                'optgroup' => 0,
                'option' => 0,
                'p' => 2,
                'param' => 0,
                'pre' => 2,
                'progress' => 0,
                'q' => 0,
                's' => 0,
                'samp' => 0,
                'script' => 0,
                'section' => 0,
                'select' => 0,
                'small' => 0,
                'source' => 0,
                'span' => 0,
                'strike' => 0,
                'strong' => 2,
                'sub' => 0,
                'summary' => 0,
                'sup' => 0,
                'table' => 2,
                'tbody' => 0,
                'td' => 2,
                'textarea' => 0,
                'tfoot' => 0,
                'th' => 2,
                'thead' => 0,
                'time' => 0,
                'tr' => 2,
                'tt' => 2,
                'u' => 0,
                'ul' => 2,
                'var' => 0,
                'video' => 0);
        System::setVar('AllowableHTML', $defhtml);

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
    public function upgrade($oldversion)
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
                System::setVar('useids', 0);
                System::setVar('idsfilter', 'xml');               // filter type
                System::setVar('idsimpactthresholdone', 1);       // db logging
                System::setVar('idsimpactthresholdtwo', 10);      // mail admin
                System::setVar('idsimpactthresholdthree', 25);    // block request
                System::setVar('idsimpactthresholdfour', 75);     // kick user, destroy session
                System::setVar('idsimpactmode', 1);               // per request per default
            // fall through

            case '1.4':
                // Location of HTML Purifier
                System::setVar('htmlpurifierlocation', 'system/SecurityCenter/lib/vendor/htmlpurifier/');

                System::setVar('idssoftblock', 0);
                System::setVar('idshtmlfields', array('POST.__wysiwyg'));
                System::setVar('idsjsonfields', array('POST.__jsondata'));
                System::setVar('idsexceptions', array(  'GET.__utmz',
                        'GET.__utmc',
                        'REQUEST.linksorder', 'POST.linksorder',
                        'REQUEST.fullcontent', 'POST.fullcontent',
                        'REQUEST.summarycontent', 'POST.summarycontent',
                        'REQUEST.filter.page', 'POST.filter.page',
                        'REQUEST.filter.value', 'POST.filter.value'));
            // fall through
                
            case '1.4.1':
                System::delVar('htmlpurifierConfig');
                // HTML Purifier default settings
                $purifierDefaultConfig = SecurityCenter_Api_User::getpurifierconfig(array('forcedefault' => true));
                $this->setVar('htmlpurifierConfig', serialize($purifierDefaultConfig));
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
    public function uninstall()
    {
        // Deletion fail - we dont want users disabling this module!
        return false;
    }
}