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
use Zikula\SecurityCenterModule\Api\ApiInterface\HtmlFilterApiInterface;

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
        $this->setSystemVar('sessionstoretofile', Constant::SESSION_STORAGE_FILE);
        $this->setSystemVar('sessionsavepath', '');
        $this->setSystemVar('gc_probability', 100);
        $this->setSystemVar('sessioncsrftokenonetime', 1); // 1 means use same token for entire session
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
        $this->setSystemVar('idssoftblock', 1); // do not block requests, but warn for debugging
        $this->setSystemVar('idsfilter', 'xml'); // filter type
        $this->setSystemVar('idsimpactthresholdone', 1); // db logging
        $this->setSystemVar('idsimpactthresholdtwo', 10); // mail admin
        $this->setSystemVar('idsimpactthresholdthree', 25); // block request
        $this->setSystemVar('idsimpactthresholdfour', 75); // kick user, destroy session
        $this->setSystemVar('idsimpactmode', 1); // per request per default
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
            '!--' => HtmlFilterApiInterface::TAG_ALLOWED_WITH_ATTRIBUTES,
            'a' => HtmlFilterApiInterface::TAG_ALLOWED_WITH_ATTRIBUTES,
            'abbr' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'acronym' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'address' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'applet' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'area' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'article' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'aside' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'audio' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'b' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'base' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'basefont' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'bdo' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'big' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'blockquote' => HtmlFilterApiInterface::TAG_ALLOWED_WITH_ATTRIBUTES,
            'br' => HtmlFilterApiInterface::TAG_ALLOWED_WITH_ATTRIBUTES,
            'button' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'canvas' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'caption' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'center' => HtmlFilterApiInterface::TAG_ALLOWED_WITH_ATTRIBUTES,
            'cite' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'code' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'col' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'colgroup' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'command' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'datalist' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'dd' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'del' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'details' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'dfn' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'dir' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'div' => HtmlFilterApiInterface::TAG_ALLOWED_WITH_ATTRIBUTES,
            'dl' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'dt' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'em' => HtmlFilterApiInterface::TAG_ALLOWED_WITH_ATTRIBUTES,
            'embed' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'fieldset' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'figcaption' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'figure' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'footer' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'font' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'form' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'h1' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'h2' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'h3' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'h4' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'h5' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'h6' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'header' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'hgroup' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'hr' => HtmlFilterApiInterface::TAG_ALLOWED_WITH_ATTRIBUTES,
            'i' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'iframe' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'img' => HtmlFilterApiInterface::TAG_ALLOWED_WITH_ATTRIBUTES,
            'input' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'ins' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'keygen' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'kbd' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'label' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'legend' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'li' => HtmlFilterApiInterface::TAG_ALLOWED_WITH_ATTRIBUTES,
            'map' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'mark' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'menu' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'marquee' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'meter' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'nav' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'nobr' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'object' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'ol' => HtmlFilterApiInterface::TAG_ALLOWED_WITH_ATTRIBUTES,
            'optgroup' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'option' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'output' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'p' => HtmlFilterApiInterface::TAG_ALLOWED_WITH_ATTRIBUTES,
            'param' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'pre' => HtmlFilterApiInterface::TAG_ALLOWED_WITH_ATTRIBUTES,
            'progress' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'q' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'rp' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'rt' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'ruby' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            's' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'samp' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'script' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'section' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'select' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'small' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'source' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'span' => HtmlFilterApiInterface::TAG_ALLOWED_WITH_ATTRIBUTES,
            'strike' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'strong' => HtmlFilterApiInterface::TAG_ALLOWED_WITH_ATTRIBUTES,
            'sub' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'summary' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'sup' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'table' => HtmlFilterApiInterface::TAG_ALLOWED_WITH_ATTRIBUTES,
            'tbody' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'td' => HtmlFilterApiInterface::TAG_ALLOWED_WITH_ATTRIBUTES,
            'textarea' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'tfoot' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'th' => HtmlFilterApiInterface::TAG_ALLOWED_WITH_ATTRIBUTES,
            'thead' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'time' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'tr' => HtmlFilterApiInterface::TAG_ALLOWED_WITH_ATTRIBUTES,
            'tt' => HtmlFilterApiInterface::TAG_ALLOWED_WITH_ATTRIBUTES,
            'u' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'ul' => HtmlFilterApiInterface::TAG_ALLOWED_WITH_ATTRIBUTES,
            'var' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'video' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'wbr' => HtmlFilterApiInterface::TAG_NOT_ALLOWED
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
                // set the session information in /src/app/config/dynamic/generated.yml
                $configDumper = $this->container->get('zikula.dynamic_config_dumper');
                $sessionStoreToFile = $this->container->get('zikula_extensions_module.api.variable')->getSystemVar('sessionstoretofile', Constant::SESSION_STORAGE_DATABASE);
                $sessionHandlerId = $sessionStoreToFile == Constant::SESSION_STORAGE_FILE ? 'session.handler.native_file' : 'zikula_core.bridge.http_foundation.doctrine_session_handler';
                $configDumper->setParameter('zikula.session.handler_id', $sessionHandlerId);
                $sessionStorageId = $sessionStoreToFile == Constant::SESSION_STORAGE_FILE ? 'zikula_core.bridge.http_foundation.zikula_session_storage_file' : 'zikula_core.bridge.http_foundation.zikula_session_storage_doctrine';
                $configDumper->setParameter('zikula.session.storage_id', $sessionStorageId); // Symfony default is 'session.storage.native'
                $sessionSavePath = $this->container->get('zikula_extensions_module.api.variable')->getSystemVar('sessionsavepath', '');
                $zikulaSessionSavePath = empty($sessionSavePath) ? '%kernel.cache_dir%/sessions' : $sessionSavePath;
                $configDumper->setParameter('zikula.session.save_path', $zikulaSessionSavePath);
            case '1.5.2':
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
