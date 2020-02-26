<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule;

use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\Doctrine\Helper\SchemaHelper;
use Zikula\Bundle\CoreBundle\DynamicConfigDumper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ExtensionsModule\Installer\AbstractExtensionInstaller;
use Zikula\SecurityCenterModule\Entity\IntrusionEntity;
use Zikula\SecurityCenterModule\Helper\HtmlTagsHelper;
use Zikula\SecurityCenterModule\Helper\PurifierHelper;

/**
 * Installation routines for the security center module.
 */
class SecurityCenterModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * @var DynamicConfigDumper
     */
    private $configDumper;

    /**
     * @var CacheClearer
     */
    private $cacheClearer;

    /**
     * @var PurifierHelper
     */
    private $purifierHelper;

    /**
     * @var HtmlTagsHelper
     */
    private $htmlTagsHelper;

    public function __construct(
        DynamicConfigDumper $configDumper,
        CacheClearer $cacheClearer,
        PurifierHelper $purifierHelper,
        AbstractExtension $extension,
        ManagerRegistry $managerRegistry,
        SchemaHelper $schemaTool,
        RequestStack $requestStack,
        TranslatorInterface $translator,
        VariableApiInterface $variableApi,
        HtmlTagsHelper $htmlTagsHelper
    ) {
        $this->configDumper = $configDumper;
        $this->cacheClearer = $cacheClearer;
        $this->purifierHelper = $purifierHelper;
        $this->htmlTagsHelper = $htmlTagsHelper;
        parent::__construct($extension, $managerRegistry, $schemaTool, $requestStack, $translator, $variableApi);
    }

    public function install(): bool
    {
        // create the table
        try {
            $this->schemaTool->create([
                IntrusionEntity::class
            ]);
        } catch (Exception $exception) {
            return false;
        }

        // Set up an initial value for a module variable.
        $this->setVar('itemsperpage', 10);

        // We use config vars for the rest of the configuration as config vars
        $this->setSystemVar('updatecheck', 1);
        $this->setSystemVar('updatefrequency', 7);
        $this->setSystemVar('updatelastchecked', 0);
        $this->setSystemVar('updateversion', ZikulaKernel::VERSION);
        $this->setSystemVar('seclevel', 'Medium');
        $this->setSystemVar('secmeddays', 7);
        $this->setSystemVar('secinactivemins', 20);
        $this->setSystemVar('sessionstoretofile', Constant::SESSION_STORAGE_FILE);
        $this->setSystemVar('sessionsavepath');
        $this->setSystemVar('gc_probability', 100);
        $this->setSystemVar('sessionregenerate', 1);
        $this->setSystemVar('sessionregeneratefreq', 10);
        $this->setSystemVar('sessionname', '_zsid');

        $this->setSystemVar('filtergetvars', 1);
        $this->setSystemVar('filterpostvars', 1);
        $this->setSystemVar('filtercookievars', 1);

        // HTML Purifier cache dir
        $this->cacheClearer->clear('purifier');

        // HTML Purifier default settings
        $purifierDefaultConfig = $this->purifierHelper->getPurifierConfig(['forcedefault' => true]);
        $this->setVar('htmlpurifierConfig', serialize($purifierDefaultConfig));

        // create vars for phpids usage
        $this->setSystemVar('useids', 0);
        $this->setSystemVar('idsmail', 0);
        $this->setSystemVar('idsrulepath', 'Resources/config/phpids_zikula_default.xml');
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
        $this->setSystemVar('idscachingtype', 'none');
        $this->setSystemVar('idscachingexpiration', 600);

        $this->setSystemVar('outputfilter', 1);

        $this->setSystemVar('htmlentities', 1);
        $this->setSystemVar('AllowableHTML', $this->htmlTagsHelper->getDefaultValues());

        // Initialisation successful
        return true;
    }

    public function upgrade(string $oldVersion): bool
    {
        switch ($oldVersion) {
            case '1.5.0':
                // avoid storing absolute pathes in module vars

                // delete obsolete variable
                $this->getVariableApi()->del(VariableApi::CONFIG, 'htmlpurifierlocation');

                // only update this value if it has not been customised
                if (false !== mb_strpos($this->getVariableApi()->get(VariableApi::CONFIG, 'idsrulepath'), 'phpids_zikula_default')) {
                    $this->setSystemVar('idsrulepath', 'system/SecurityCenterModule/Resources/config/phpids_zikula_default.xml');
                }
            case '1.5.1':
                // set the session information in /config/dynamic/generated.yaml
                $sessionStoreToFile = $this->getVariableApi()->getSystemVar('sessionstoretofile', Constant::SESSION_STORAGE_DATABASE);
                $sessionHandlerId = Constant::SESSION_STORAGE_FILE === $sessionStoreToFile ? 'session.handler.native_file' : 'zikula_core.bridge.http_foundation.doctrine_session_handler';
                $this->configDumper->setParameter('zikula.session.handler_id', $sessionHandlerId);
                $sessionStorageId = Constant::SESSION_STORAGE_FILE === $sessionStoreToFile ? 'zikula_core.bridge.http_foundation.zikula_session_storage_file' : 'zikula_core.bridge.http_foundation.zikula_session_storage_doctrine';
                $this->configDumper->setParameter('zikula.session.storage_id', $sessionStorageId); // Symfony default is 'session.storage.native'
                $sessionSavePath = $this->getVariableApi()->getSystemVar('sessionsavepath', '');
                $zikulaSessionSavePath = empty($sessionSavePath) ? '%kernel.cache_dir%/sessions' : $sessionSavePath;
                $this->configDumper->setParameter('zikula.session.save_path', $zikulaSessionSavePath);
            case '1.5.2':
                $varsToRemove = [
                    'secure_domain',
                    'signcookies',
                    'signingkey',
                    'sessioncsrftokenonetime',
                    'sessionipcheck',
                    'keyexpiry',
                    'sessionauthkeyua',
                    'gc_probability',
                    'sessionrandregenerate',
                    'sessionregenerate',
                    'sessionregeneratefreq'
                ];
                foreach ($varsToRemove as $varName) {
                    $this->getVariableApi()->del(VariableApi::CONFIG, $varName);
                }
                $this->setSystemVar('idsrulepath', 'Resources/config/phpids_zikula_default.xml');
                $this->setSystemVar('idscachingtype', 'none');
                $this->setSystemVar('idscachingexpiration', 600);

                $connection = $this->entityManager->getConnection();

                // extend length of tag field of intrusion table
                $sql = '
                    ALTER TABLE `sc_intrusion`
                    MODIFY `tag` VARCHAR(150) NOT NULL
                ';
                $stmt = $connection->prepare($sql);
                $stmt->execute();
            case '1.5.3':
                // current version
        }

        // Update successful
        return true;
    }

    public function uninstall(): bool
    {
        // this module can't be uninstalled
        return false;
    }

    private function setSystemVar(string $name, $value = ''): bool
    {
        return $this->getVariableApi()->set(VariableApi::CONFIG, $name, $value);
    }
}
