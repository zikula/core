<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule;

use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Core\AbstractExtensionInstaller;

/**
 * Installation routines for the security center module.
 */
class SecurityCenterModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * Initialise the security center module.
     *
     * @return bool true on success, false otherwise
     */
    public function install()
    {
        // create the table
        try {
            $this->schemaTool->create([
                'Zikula\SecurityCenterModule\Entity\IntrusionEntity'
            ]);
        } catch (\Exception $e) {
            return false;
        }

        // Set up an initial value for a module variable.
        $this->setVar('itemsperpage', 10);

        // We use config vars for the rest of the configuration as config vars
        $this->setSystemVar('updatecheck', 1);
        $this->setSystemVar('updatefrequency', 7);
        $this->setSystemVar('updatelastchecked', 0);
        $this->setSystemVar('updateversion', ZikulaKernel::VERSION);
        $this->setSystemVar('keyexpiry', 0);
        $this->setSystemVar('sessionauthkeyua', 0);
        $this->setSystemVar('secure_domain', '');
        $this->setSystemVar('signcookies', 1);
        $this->setSystemVar('signingkey', sha1(mt_rand(0, time())));
        $this->setSystemVar('seclevel', 'Medium');
        $this->setSystemVar('secmeddays', 7);
        $this->setSystemVar('secinactivemins', 20);
        $this->setSystemVar('sessionstoretofile', 0);
        $this->setSystemVar('sessionsavepath', '');
        $this->setSystemVar('gc_probability', 100);
        $this->setSystemVar('sessioncsrftokenonetime', 1);  // 1 means use same token for entire session
        $this->setSystemVar('anonymoussessions', 1); // @deprecated
        $this->setSystemVar('sessionrandregenerate', 1);
        $this->setSystemVar('sessionregenerate', 1);
        $this->setSystemVar('sessionregeneratefreq', 10);
        $this->setSystemVar('sessionipcheck', 0);
        $this->setSystemVar('sessionname', '_zsid');

        $this->setSystemVar('filtergetvars', 1);
        $this->setSystemVar('filterpostvars', 1);
        $this->setSystemVar('filtercookievars', 1);

        // HTML Purifier cache dir
        $this->container->get('zikula.cache_clearer')->clear('purifier');

        // HTML Purifier default settings
        $purifierDefaultConfig = $this->container->get('zikula_security_center_module.helper.purifier_helper')->getPurifierConfig(['forcedefault' => true]);
        $this->setVar('htmlpurifierConfig', serialize($purifierDefaultConfig));

        // create vars for phpids usage
        $this->setSystemVar('useids', 0);
        $this->setSystemVar('idsmail', 0);
        $this->setSystemVar('idsrulepath', 'system/SecurityCenterModule/Resources/config/phpids_zikula_default.xml');
        $this->setSystemVar('idssoftblock', 1);                // do not block requests, but warn for debugging
        $this->setSystemVar('idsfilter', 'xml');               // filter type
        $this->setSystemVar('idsimpactthresholdone', 1);       // db logging
        $this->setSystemVar('idsimpactthresholdtwo', 10);      // mail admin
        $this->setSystemVar('idsimpactthresholdthree', 25);    // block request
        $this->setSystemVar('idsimpactthresholdfour', 75);     // kick user, destroy session
        $this->setSystemVar('idsimpactmode', 1);               // per request per default
        $this->setSystemVar('idshtmlfields', ['POST.__wysiwyg']);
        $this->setSystemVar('idsjsonfields', ['POST.__jsondata']);
        $this->setSystemVar('idsexceptions', [
            'GET.__utmz',
            'GET.__utmc',
            'REQUEST.linksorder', 'POST.linksorder',
            'REQUEST.fullcontent', 'POST.fullcontent',
            'REQUEST.summarycontent', 'POST.summarycontent',
            'REQUEST.filter.page', 'POST.filter.page',
            'REQUEST.filter.value', 'POST.filter.value'
        ]);

        $this->setSystemVar('outputfilter', 1);

        $this->setSystemVar('htmlentities', 1);

        // default values for AllowableHTML
        $defhtml = [
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
        ];
        $this->setSystemVar('AllowableHTML', $defhtml);

        // Initialisation successful
        return true;
    }

    /**
     * upgrade the SecurityCenter module from an old version
     *
     * @param string $oldVersion version number string to upgrade from
     *
     * @return bool|string true on success, last valid version string or false if fails
     */
    public function upgrade($oldVersion)
    {
        switch ($oldVersion) {
            case '1.5.0':
                // avoid storing absolute pathes in module vars
                $variableApi = $this->container->get('zikula_extensions_module.api.variable');

                // delete obsolete variable
                $variableApi->del(VariableApi::CONFIG, 'htmlpurifierlocation');

                // only update this value if it has not been customised
                if (false !== strpos($variableApi->get(VariableApi::CONFIG, 'idsrulepath'), 'phpids_zikula_default')) {
                    $this->setSystemVar('idsrulepath', 'system/SecurityCenterModule/Resources/config/phpids_zikula_default.xml');
                }
            case '1.5.1':
                // current version
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

    private function setSystemVar($name, $value = '')
    {
        return $this->container->get('zikula_extensions_module.api.variable')->set(VariableApi::CONFIG, $name, $value);
    }
}
