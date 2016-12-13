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

use RandomLib\Factory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\BlocksModule\Entity\BlockPlacementEntity;
use Zikula\Bundle\CoreBundle\Bundle\AbstractCoreModule;
use Zikula\Bundle\CoreBundle\Bundle\Bootstrap as CoreBundleBootstrap;
use Zikula\Core\Event\GenericEvent;
use Zikula\ExtensionsModule\Api\ExtensionApi;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Core\CoreEvents;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
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
            case "menu":
                return $this->installModule('ZikulaMenuModule');
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
        /** @var AbstractCoreModule $module */
        $className = $module->getInstallerClass();
        $reflectionInstaller = new \ReflectionClass($className);
        $installer = $reflectionInstaller->newInstance();
        $installer->setBundle($module);
        if ($installer instanceof ContainerAwareInterface) {
            $installer->setContainer($this->container);
        }

        if ($installer->install()) {
            return true;
        }

        return false;
    }

    private function activateModules()
    {
        $extensionsInFileSystem = $this->container->get('zikula_extensions_module.bundle_sync_helper')->scanForBundles(['system']);
        $this->container->get('zikula_extensions_module.bundle_sync_helper')->syncExtensions($extensionsInFileSystem);

        /** @var ExtensionEntity[] $extensions */
        $extensions = $this->container->get('zikula_extensions_module.extension_repository')->findAll();
        foreach ($extensions as $extension) {
            if ($extension->getName() != 'ZikulaPageLockModule' && \ZikulaKernel::isCoreModule($extension->getName())) {
                $extension->setState(ExtensionApi::STATE_ACTIVE);
            }
        }
        $this->container->get('doctrine.orm.entity_manager')->flush();

        return true;
    }

    private function categorizeModules()
    {
        reset(\ZikulaKernel::$coreModules);
        $systemModulesCategories = [
            'ZikulaExtensionsModule' => $this->translator->__('System'),
            'ZikulaPermissionsModule' => $this->translator->__('Users'),
            'ZikulaGroupsModule' => $this->translator->__('Users'),
            'ZikulaBlocksModule' => $this->translator->__('Layout'),
            'ZikulaUsersModule' => $this->translator->__('Users'),
            'ZikulaZAuthModule' => $this->translator->__('Users'),
            'ZikulaThemeModule' => $this->translator->__('Layout'),
            'ZikulaSecurityCenterModule' => $this->translator->__('Security'),
            'ZikulaCategoriesModule' => $this->translator->__('Content'),
            'ZikulaMailerModule' => $this->translator->__('System'),
            'ZikulaSearchModule' => $this->translator->__('Content'),
            'ZikulaAdminModule' => $this->translator->__('System'),
            'ZikulaSettingsModule' => $this->translator->__('System'),
            'ZikulaRoutesModule' => $this->translator->__('System'),
            'ZikulaMenuModule' => $this->translator->__('Content'),
            'ZikulaPageLockModule' => $this->translator->__('Content'),
        ];

        $modulesCategories = $this->container->get('doctrine')
            ->getRepository('ZikulaAdminModule:AdminCategoryEntity')->getIndexedCollection('name');

        foreach (\ZikulaKernel::$coreModules as $systemModule => $bundleClass) {
            $category = $systemModulesCategories[$systemModule];
            $this->container->get('doctrine')
                ->getRepository('ZikulaAdminModule:AdminModuleEntity')
                ->setModuleCategory($systemModule, $modulesCategories[$category]);
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
        $this->createMainMenuBlock();

        return true;
    }

    /**
     * This function inserts the admin's user data
     */
    private function updateAdmin()
    {
        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');
        $params = $this->decodeParameters($this->yamlManager->getParameters());

        $nowUTC = new \DateTime(null, new \DateTimeZone('UTC'));
        $nowUTCStr = $nowUTC->format(UsersConstant::DATETIME_FORMAT);

        /** @var \Zikula\UsersModule\Entity\UserEntity $userEntity */
        $userEntity = $entityManager->find('ZikulaUsersModule:UserEntity', 2);
        $userEntity->setUname($params['username']);
        $userEntity->setEmail($params['email']);
        $userEntity->setActivated(1);
        $userEntity->setUser_Regdate($nowUTCStr);
        $userEntity->setLastlogin($nowUTCStr);
        $entityManager->persist($userEntity);

        $mapping = new AuthenticationMappingEntity();
        $mapping->setUid($userEntity->getUid());
        $mapping->setUname($userEntity->getUname());
        $mapping->setEmail($userEntity->getEmail());
        $mapping->setVerifiedEmail(true);
        $mapping->setPass($this->container->get('zikula_zauth_module.api.password')->getHashedPassword($params['password']));
        $mapping->setMethod(ZAuthConstant::AUTHENTICATION_METHOD_UNAME);
        $entityManager->persist($mapping);

        $entityManager->flush();

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
        $params = $this->decodeParameters($this->yamlManager->getParameters());
        $variableApi = $this->container->get('zikula_extensions_module.api.variable');
        $variableApi->getAll(VariableApi::CONFIG); // forces initialization of API
        $variableApi->set(VariableApi::CONFIG, 'language_i18n', $params['locale']);
        // Set the System Identifier as a unique string.
        $variableApi->set(VariableApi::CONFIG, 'system_identifier', str_replace('.', '', uniqid(rand(1000000000, 9999999999), true)));
        // add admin email as site email
        $variableApi->set(VariableApi::CONFIG, 'adminmail', $params['email']);
        // regenerate the theme list
        $this->container->get('zikula_theme_module.helper.bundle_sync_helper')->regenerate();

        // add remaining parameters and remove unneeded ones
        unset($params['username'], $params['password'], $params['email'], $params['dbtabletype']);
        $params['datadir'] = !empty($params['datadir']) ? $params['datadir'] : 'userdir';
        $RandomLibFactory = new Factory();
        $generator = $RandomLibFactory->getMediumStrengthGenerator();
        $params['secret'] = $generator->generateString(50);
        $params['url_secret'] = $generator->generateString(10);
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
        // this stage is now disabled because the routes do not need to be reloaded. @todo refactor and remove

        return true;
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
        $params[\ZikulaKernel::CORE_INSTALLED_VERSION_PARAM] = \ZikulaKernel::VERSION;

        $this->yamlManager->setParameters($params);
        // clear the cache
        $this->container->get('zikula.cache_clearer')->clear('symfony.config');

        return true;
    }

    private function createMainMenuBlock()
    {
        // Create the Main Menu Block
        $_em = $this->container->get('doctrine')->getManager();
        $menuModuleEntity = $_em->getRepository('ZikulaExtensionsModule:ExtensionEntity')->findOneBy(['name' => 'ZikulaMenuModule']);
        $blockEntity = new BlockEntity();
        $mainMenuString = $this->translator->__('Main menu');
        $blockEntity->setTitle($mainMenuString);
        $blockEntity->setBkey('ZikulaMenuModule:\Zikula\MenuModule\Block\MenuBlock');
        $blockEntity->setBlocktype('Menu');
        $blockEntity->setDescription($mainMenuString);
        $blockEntity->setModule($menuModuleEntity);
        $blockEntity->setProperties([
            'name' => 'mainMenu',
            'options' => '{"template": "ZikulaMenuModule:Override:bootstrap_fontawesome.html.twig"}'
        ]);
        $_em->persist($blockEntity);

        $topNavPosition = $_em->getRepository('ZikulaBlocksModule:BlockPositionEntity')->findOneBy(['name' => 'topnav']);
        $placement = new BlockPlacementEntity();
        $placement->setBlock($blockEntity);
        $placement->setPosition($topNavPosition);
        $placement->setSortorder(0);
        $_em->persist($placement);

        $_em->flush();
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
