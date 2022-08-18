<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\Configurator;
use Zikula\Bundle\CoreBundle\Doctrine\Helper\SchemaHelper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ExtensionsModule\Installer\AbstractExtensionInstaller;
use Zikula\SecurityCenterModule\Helper\HtmlTagsHelper;
use Zikula\SecurityCenterModule\Helper\PurifierHelper;

class SecurityCenterModuleInstaller extends AbstractExtensionInstaller
{
    public function __construct(
        private readonly CacheClearer $cacheClearer,
        private readonly PurifierHelper $purifierHelper,
        private readonly HtmlTagsHelper $htmlTagsHelper,
        private readonly string $projectDir,
        AbstractExtension $extension,
        ManagerRegistry $managerRegistry,
        SchemaHelper $schemaTool,
        RequestStack $requestStack,
        TranslatorInterface $translator,
        VariableApiInterface $variableApi
    ) {
        parent::__construct($extension, $managerRegistry, $schemaTool, $requestStack, $translator, $variableApi);
    }

    public function install(): bool
    {
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

        $this->setSystemVar('outputfilter', 1);

        $this->setSystemVar('htmlentities', 1);
        $this->setSystemVar('AllowableHTML', $this->htmlTagsHelper->getDefaultValues());

        return true;
    }

    public function upgrade(string $oldVersion): bool
    {
        switch ($oldVersion) {
            case '1.5.0': // shipped with Core-1.4.3
                $this->getVariableApi()->del(VariableApi::CONFIG, 'htmlpurifierlocation');
                // no break
            case '1.5.1':
                // set the session information in /config/dynamic/generated.yaml
                $sessionStoreToFile = $this->getVariableApi()->getSystemVar('sessionstoretofile', Constant::SESSION_STORAGE_DATABASE);
                $sessionSavePath = $this->getVariableApi()->getSystemVar('sessionsavepath', '');
                $configurator = new Configurator($this->projectDir);
                $configurator->loadPackages('zikula_security_center');
                $sessionConfig = $configurator->get('zikula_security_center', 'session');
                $sessionConfig['handler_id'] = Constant::SESSION_STORAGE_FILE === $sessionStoreToFile ? 'session.handler.native_file' : 'zikula_core.bridge.http_foundation.doctrine_session_handler';
                $sessionConfig['storage_id'] = Constant::SESSION_STORAGE_FILE === $sessionStoreToFile ? 'zikula_core.bridge.http_foundation.zikula_session_storage_file' : 'zikula_core.bridge.http_foundation.zikula_session_storage_doctrine';
                $sessionConfig['save_path'] = empty($sessionSavePath) ? '%kernel.cache_dir%/sessions' : $sessionSavePath;
                $configurator->set('zikula_security_center', 'session', $sessionConfig);
                $configurator->write();

                // no break
            case '1.5.2': // shipped with Core-2.0.15
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
        }

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
