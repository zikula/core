<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Zikula\Bundle\CoreBundle\Bundle\Bootstrap as CoreBundleBootstrap;
use Zikula\Core\Event\GenericEvent;
use Zikula\ExtensionsModule\Api\ExtensionApi;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Core\Event\ModuleStateEvent;
use Zikula\Core\CoreEvents;
use Zikula\ZAuthModule\ZAuthConstant;

/**
 * Class AjaxInstallController
 */
class AjaxInstallController extends AbstractController
{
    /**
     * @var YamlDumper
     */
    private $yamlManager;

    private $systemModules = [
        'ZikulaExtensionsModule',
        'ZikulaSettingsModule',
        'ZikulaThemeModule',
        'ZikulaAdminModule',
        'ZikulaPermissionsModule',
        'ZikulaGroupsModule',
        'ZikulaBlocksModule',
        'ZikulaUsersModule',
        'ZikulaZAuthModule',
        'ZikulaSecurityCenterModule',
        'ZikulaCategoriesModule',
        'ZikulaMailerModule',
        'ZikulaSearchModule',
        'ZikulaRoutesModule',
    ];

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->yamlManager = new YamlDumper($this->container->get('kernel')->getRootDir() .'/config', 'custom_parameters.yml');
    }

    public function ajaxAction(Request $request)
    {
        $stage = $request->request->get('stage');
        $status = $this->executeStage($stage);

        return new JsonResponse(['status' => $status]);
    }

    public function commandLineAction($stage)
    {
        return $this->executeStage($stage);
    }

    private function executeStage($stageName)
    {
        switch ($stageName) {
            case "bundles":
                return $this->createBundles();
            case "install_event":
                return $this->fireEvent(CoreEvents::CORE_INSTALL_PRE_MODULE);
            case "extensions":
                return $this->installModule('ZikulaExtensionsModule');
            case "settings":
                return $this->installModule('ZikulaSettingsModule');
            case "theme":
                return $this->installModule('ZikulaThemeModule');
            case "admin":
                return $this->installModule('ZikulaAdminModule');
            case "permissions":
                return $this->installModule('ZikulaPermissionsModule');
            case "groups":
                return $this->installModule('ZikulaGroupsModule');
            case "blocks":
                return $this->installModule('ZikulaBlocksModule');
            case "users":
                return $this->installModule('ZikulaUsersModule');
            case "zauth":
                return $this->installModule('ZikulaZAuthModule');
            case "security":
                return $this->installModule('ZikulaSecurityCenterModule');
            case "categories":
                return $this->installModule('ZikulaCategoriesModule');
            case "mailer":
                return $this->installModule('ZikulaMailerModule');
            case "search":
                return $this->installModule('ZikulaSearchModule');
            case "routes":
                return $this->installModule('ZikulaRoutesModule');
            case "updateadmin":
                return $this->updateAdmin();
            case "loginadmin":
                return $this->loginAdmin();
            case "activatemodules":
                return $this->activateModules();
            case "categorize":
                return $this->categorizeModules();
            case "createblocks":
                return $this->createBlocks();
            case "finalizeparameters":
                return $this->finalizeParameters();
            case "reloadroutes":
                return $this->reloadRoutes();
            case "plugins":
                return $this->installPlugins();
            case "installassets":
                return $this->installAssets();
            case "protect":
                return $this->protectFiles();
        }
        \System::setInstalling(false);

        return true;
    }

    private function createBundles()
    {
        $kernel = $this->container->get('kernel');
        $boot = new CoreBundleBootstrap();
        $helper = $this->container->get('zikula_core.internal.bootstrap_helper');
        $helper->createSchema();
        $helper->load();
        $bundles = [];
        // this neatly autoloads
        $boot->getPersistedBundles($kernel, $bundles);

        return true;
    }

    /**
     * public because called by AjaxUpgradeController also
     * @param $moduleName
     * @return bool
     */
    public function installModule($moduleName)
    {
        $module = $this->container->get('kernel')->getModule($moduleName);
        /** @var \Zikula\Core\AbstractModule $module */
        $className = $module->getInstallerClass();
        $bootstrap = $module->getPath().'/bootstrap.php';
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }

        // support both Legacy and Core-2.0 Spec system modules until fully refactored.
        // @todo remove legacy support when refactoring is complete.
        $reflectionInstaller = new \ReflectionClass($className);
        if ($reflectionInstaller->isSubclassOf('Zikula_AbstractInstaller')) {
            $installer = $reflectionInstaller->newInstanceArgs([$this->container, $module]);
        } elseif ($reflectionInstaller->isSubclassOf('\Zikula\Core\ExtensionInstallerInterface')) {
            $installer = $reflectionInstaller->newInstance();
            $installer->setBundle($module);
            if ($installer instanceof ContainerAwareInterface) {
                $installer->setContainer($this->container);
            }
        } else {
            throw new \Exception('Installer class must be subclass of Zikula_AbstractInstaller or implement Zikula\Core\ExtensionInstallerInterface.');
        }

        if ($installer->install()) {
            return true;
        }

        return false;
    }

    private function activateModules()
    {
        $extensionsInFileSystem = $this->container->get('zikula_extensions_module.bundle_sync_helper')->scanForBundles();
        $this->container->get('zikula_extensions_module.bundle_sync_helper')->syncExtensions($extensionsInFileSystem);

        /** @var ExtensionEntity[] $extensions */
        $extensions = $this->container->get('zikula_extensions_module.extension_repository')->findAll();
        foreach ($extensions as $extension) {
            if (in_array($extension->getName(), $this->systemModules)) {
                $extension->setState(ExtensionApi::STATE_ACTIVE);
            }
        }
        $this->container->get('doctrine.orm.entity_manager')->flush();

        return true;
    }

    private function categorizeModules()
    {
        reset($this->systemModules);
        $systemModulesCategories = [
            'ZikulaExtensionsModule' => __('System'),
            'ZikulaPermissionsModule' => __('Users'),
            'ZikulaGroupsModule' => __('Users'),
            'ZikulaBlocksModule' => __('Layout'),
            'ZikulaUsersModule' => __('Users'),
            'ZikulaZAuthModule' => __('Users'),
            'ZikulaThemeModule' => __('Layout'),
            'ZikulaSecurityCenterModule' => __('Security'),
            'ZikulaCategoriesModule' => __('Content'),
            'ZikulaMailerModule' => __('System'),
            'ZikulaSearchModule' => __('Content'),
            'ZikulaAdminModule' => __('System'),
            'ZikulaSettingsModule' => __('System'),
            'ZikulaRoutesModule' => __('System')
        ];

        $modulesCategories = $this->container->get('doctrine.orm.entity_manager')
            ->getRepository('ZikulaAdminModule:AdminCategoryEntity')->getIndexedCollection('name');

        foreach ($this->systemModules as $systemModule) {
            $category = $systemModulesCategories[$systemModule];
            \ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'addmodtocategory', [
                'module' => $systemModule,
                'category' => $modulesCategories[$category]->getCid()
            ]);
        }

        return true;
    }

    private function createBlocks()
    {
        $installer = new \Zikula\BlocksModule\BlocksModuleInstaller();
        $installer->setBundle($this->container->get('kernel')->getModule('ZikulaBlocksModule'));
        $installer->setContainer($this->container);
        // create the default blocks.
        $installer->defaultdata();

        return true;
    }

    /**
     * This function inserts the admin's user data
     */
    private function updateAdmin()
    {
        $em = $this->container->get('doctrine.entitymanager');
        $params = $this->decodeParameters($this->yamlManager->getParameters());

        // @todo this must be updated to use ZAuth

        // create the password hash
        $password = \UserUtil::getHashedPassword($params['password'], \UserUtil::getPasswordHashMethodCode(ZAuthConstant::DEFAULT_HASH_METHOD));

        // prepare the data
        $username = mb_strtolower($params['username']);

        $nowUTC = new \DateTime(null, new \DateTimeZone('UTC'));
        $nowUTCStr = $nowUTC->format(UsersConstant::DATETIME_FORMAT);

        /** @var \Zikula\UsersModule\Entity\UserEntity $entity */
        $entity = $em->find('ZikulaUsersModule:UserEntity', 2);
        $entity->setUname($username);
        $entity->setEmail($params['email']);
        $entity->setPass($password);
        $entity->setActivated(1);
        $entity->setUser_Regdate($nowUTCStr);
        $entity->setLastlogin($nowUTCStr);
        $em->persist($entity);

        $em->flush();

        return true;
    }

    /**
     * public because called by AjaxUpgradeController also
     * @return bool
     */
    public function loginAdmin()
    {
        $params = $this->decodeParameters($this->yamlManager->getParameters());
        $user = $this->container->get('zikula_users_module.user_repository')->findOneBy(['uname' => $params['username']]);
        $request = $this->container->get('request_stack')->getCurrentRequest();
        if (isset($request) && $request->hasSession()) {
            $this->container->get('zikula_users_module.helper.access_helper')->login($user, true);
        }

        return true;
    }

    private function finalizeParameters()
    {
        \ModUtil::initCoreVars(true); // initialize the modvars array (includes ZConfig (System) vars)
        $params = $this->decodeParameters($this->yamlManager->getParameters());

        $this->container->get('zikula_extensions_module.api.variable')->set(VariableApi::CONFIG, 'language_i18n', $params['locale']);
        // Set the System Identifier as a unique string.
        $this->container->get('zikula_extensions_module.api.variable')->set(VariableApi::CONFIG, 'system_identifier', str_replace('.', '', uniqid(rand(1000000000, 9999999999), true)));
        // add admin email as site email
        $this->container->get('zikula_extensions_module.api.variable')->set(VariableApi::CONFIG, 'adminmail', $params['email']);
        // regenerate the theme list
        $this->container->get('zikula_theme_module.helper.bundle_sync_helper')->regenerate();

        // add remaining parameters and remove unneeded ones
        unset($params['username'], $params['password'], $params['email'], $params['dbtabletype']);
        $params['datadir'] = !empty($params['datadir']) ? $params['datadir'] : 'userdir';
        $params['secret'] = \RandomUtil::getRandomString(50);
        $params['url_secret'] = \RandomUtil::getRandomString(10);
        // Configure the Request Context
        // see http://symfony.com/doc/current/cookbook/console/sending_emails.html#configuring-the-request-context-globally
        $params['router.request_context.host'] = isset($params['router.request_context.host']) ? $params['router.request_context.host'] : $this->container->get('request')->getHost();
        $params['router.request_context.scheme'] = isset($params['router.request_context.scheme']) ? $params['router.request_context.scheme'] : 'http';
        $params['router.request_context.base_url'] = isset($params['router.request_context.base_url']) ? $params['router.request_context.base_url'] : $this->container->get('request')->getBasePath();
        $params['umask'] = isset($params['umask']) ? $params['umask'] : null;
        $this->yamlManager->setParameters($params);

        // clear the cache
        $this->container->get('zikula.cache_clearer')->clear('symfony.config');

        return true;
    }

    /**
     * remove base64 encoding for admin params
     *
     * @param $params
     * @return mixed
     */
    private function decodeParameters($params)
    {
        if (!empty($params['password'])) {
            $params['password'] = base64_decode($params['password']);
        }
        if (!empty($params['username'])) {
            $params['username'] = base64_decode($params['username']);
        }
        if (!empty($params['email'])) {
            $params['email'] = base64_decode($params['email']);
        }

        return $params;
    }

    /**
     * public because called by AjaxUpgradeController also
     * @return bool
     */
    public function reloadRoutes()
    {
        // fire MODULE_INSTALL event to reload all routes
        $event = new ModuleStateEvent($this->container->get('kernel')->getModule('ZikulaRoutesModule'));
        $this->container->get('event_dispatcher')->dispatch(CoreEvents::MODULE_POSTINSTALL, $event);

        return true;
    }

    private function installPlugins()
    {
        $result = true;
        $systemPlugins = \PluginUtil::loadAllSystemPlugins();
        foreach ($systemPlugins as $plugin) {
            $result = $result && \PluginUtil::install($plugin);
        }

        return $result;
    }

    private function installAssets()
    {
        $this->container->get('zikula_extensions_module.extension_helper')->installAssets();

        return true;
    }

    private function protectFiles()
    {
        // protect config.php and parameters.yml files
        foreach ([
            realpath($this->container->get('kernel')->getRootDir() . '/../config/config.php'),
            realpath($this->container->get('kernel')->getRootDir() . '/../app/config/parameters.yml')
        ] as $file) {
            @chmod($file, 0400);
            if (!is_readable($file)) {
                @chmod($file, 0440);
                if (!is_readable($file)) {
                    @chmod($file, 0444);
                }
            }
        }

        // set installed = true
        $params = $this->yamlManager->getParameters();
        $params['installed'] = true;
        // set currently installed version into parameters
        $params[\Zikula_Core::CORE_INSTALLED_VERSION_PARAM] = \Zikula_Core::VERSION_NUM;

        $this->yamlManager->setParameters($params);
        // clear the cache
        $this->container->get('zikula.cache_clearer')->clear('symfony.config');

        return true;
    }

    private function fireEvent($eventName)
    {
        $event = new GenericEvent();
        $this->container->get('event_dispatcher')->dispatch($eventName, $event);
        if ($event->isPropagationStopped()) {
            return false;
        }

        return true;
    }
}
