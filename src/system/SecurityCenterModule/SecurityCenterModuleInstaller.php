<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule;

use System;
use Zikula_Core;
use CacheUtil;
use Zikula\SecurityCenterModule\Util as SecurityCenterUtil;
use DoctrineHelper;

/**
 * Installation routines for the security center module
 */
class SecurityCenterModuleInstaller extends \Zikula_AbstractInstaller
{
    /**
     * initialise the SecurityCenter module
     *
     * @return bool true on success, false otherwise
     */
    public function install()
    {
        // create the table
        try {
            DoctrineHelper::createSchema($this->entityManager, array('Zikula\SecurityCenterModule\Entity\IntrusionEntity'));
        } catch (\Exception $e) {
            return false;
        }

        // Set up an initial value for a module variable.
        $this->setVar('itemsperpage', 10);

        // We use config vars for the rest of the configuration as config vars
        System::setVar('updatecheck', 1);
        System::setVar('updatefrequency', 7);
        System::setVar('updatelastchecked', 0);
        System::setVar('updateversion', Zikula_Core::VERSION_NUM);
        System::setVar('keyexpiry', 0);
        System::setVar('sessionauthkeyua', false);
        System::setVar('secure_domain', '');
        System::setVar('signcookies', 1);
        System::setVar('signingkey', sha1(mt_rand(0, time())));
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
        System::setVar('sessionname', '_zsid');
        System::setVar('sessioncsrftokenonetime', 1);  // 1 means use same token for entire session

        System::setVar('filtergetvars', 1);
        System::setVar('filterpostvars', 1);
        System::setVar('filtercookievars', 1);
        System::setVar('outputfilter', 1);

        // Location of HTML Purifier
        System::setVar('htmlpurifierlocation', __DIR__.'/vendor/htmlpurifier/');

        // HTML Purifier cache dir
        $purifierCacheDir = CacheUtil::getLocalDir() . '/purifierCache';
        if (!file_exists($purifierCacheDir)) {
            CacheUtil::clearLocalDir('purifierCache');
        }

        // HTML Purifier default settings
        $purifierDefaultConfig = SecurityCenterUtil::getpurifierconfig(array('forcedefault' => true));
        $this->setVar('htmlpurifierConfig', serialize($purifierDefaultConfig));

        // create vars for phpids usage
        System::setVar('useids', 0);
        System::setVar('idsmail', 0);
        System::setVar('idsrulepath', __DIR__.'/Resources/config/phpids_zikula_default.xml');
        System::setVar('idssoftblock', 1);                // do not block requests, but warn for debugging
        System::setVar('idsfilter', 'xml');               // filter type
        System::setVar('idsimpactthresholdone', 1);       // db logging
        System::setVar('idsimpactthresholdtwo', 10);      // mail admin
        System::setVar('idsimpactthresholdthree', 25);    // block request
        System::setVar('idsimpactthresholdfour', 75);     // kick user, destroy session
        System::setVar('idsimpactmode', 1);               // per request per default
        System::setVar('idshtmlfields', array('POST.__wysiwyg'));
        System::setVar('idsjsonfields', array('POST.__jsondata'));
        System::setVar('idsexceptions', array('GET.__utmz',
                        'GET.__utmc',
                        'REQUEST.linksorder', 'POST.linksorder',
                        'REQUEST.fullcontent', 'POST.fullcontent',
                        'REQUEST.summarycontent', 'POST.summarycontent',
                        'REQUEST.filter.page', 'POST.filter.page',
                        'REQUEST.filter.value', 'POST.filter.value'));

        System::setVar('htmlentities', '1');

        // default values for AllowableHTML
        $defhtml = array(
            '!--' => 2,
            'a' => 2,
            'abbr' => 1,
            'acronym' => 1,
            'address' => 1,
            'applet' => 0,
            'area' => 0,
            'article' => 1,
            'aside' => 1,
            'audio' => 0,
            'b' => 1,
            'base' => 0,
            'basefont' => 0,
            'bdo' => 0,
            'big' => 0,
            'blockquote' => 2,
            'br' => 2,
            'button' => 0,
            'canvas' => 0,
            'caption' => 1,
            'center' => 2,
            'cite' => 1,
            'code' => 0,
            'col' => 1,
            'colgroup' => 1,
            'command' => 0,
            'datalist' => 0,
            'dd' => 1,
            'del' => 0,
            'details' => 1,
            'dfn' => 0,
            'dir' => 0,
            'div' => 2,
            'dl' => 1,
            'dt' => 1,
            'em' => 2,
            'embed' => 0,
            'fieldset' => 1,
            'figcaption' => 0,
            'figure' => 0,
            'footer' => 0,
            'font' => 0,
            'form' => 0,
            'h1' => 1,
            'h2' => 1,
            'h3' => 1,
            'h4' => 1,
            'h5' => 1,
            'h6' => 1,
            'header' => 0,
            'hgroup' => 0,
            'hr' => 2,
            'i' => 1,
            'iframe' => 0,
            'img' => 2,
            'input' => 0,
            'ins' => 0,
            'keygen' => 0,
            'kbd' => 0,
            'label' => 1,
            'legend' => 1,
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
            'output' => 0,
            'p' => 2,
            'param' => 0,
            'pre' => 2,
            'progress' => 0,
            'q' => 0,
            'rp' => 0,
            'rt' => 0,
            'ruby' => 0,
            's' => 0,
            'samp' => 0,
            'script' => 0,
            'section' => 0,
            'select' => 0,
            'small' => 0,
            'source' => 0,
            'span' => 2,
            'strike' => 0,
            'strong' => 2,
            'sub' => 1,
            'summary' => 1,
            'sup' => 0,
            'table' => 2,
            'tbody' => 1,
            'td' => 2,
            'textarea' => 0,
            'tfoot' => 1,
            'th' => 2,
            'thead' => 0,
            'time' => 0,
            'tr' => 2,
            'tt' => 2,
            'u' => 0,
            'ul' => 2,
            'var' => 0,
            'video' => 0,
            'wbr' => 0
        );
        System::setVar('AllowableHTML', $defhtml);

        // Initialisation successful
        return true;
    }

    /**
     * upgrade the SecurityCenter module from an old version
     *
     * @param string $oldversion version number string to upgrade from
     *
     * @return bool|string true on success, last valid version string or false if fails
     */
    public function upgrade($oldversion)
    {
        switch ($oldversion) {
            case '1.4.4':
            // future upgrade routines
        }

        // Update successful
        return true;
    }

    /**
     * delete the SecurityCenter module
     *
     * @return bool true on success, false otherwise
     */
    public function uninstall()
    {
        // this module can't be uninstalled
        return false;
    }
}
