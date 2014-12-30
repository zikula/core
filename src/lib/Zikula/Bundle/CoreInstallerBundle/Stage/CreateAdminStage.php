<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula CoreInstaller bundle.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Stage;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\FormInterface;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\CreateAdminType;
use Zikula\Component\Wizard\AbortStageException;
use Zikula\Component\Wizard\FormHandlerInterface;
use Zikula\Component\Wizard\InjectContainerInterface;
use Zikula\Component\Wizard\StageInterface;
use Zikula\Module\UsersModule\Constant as UsersConstant;

class CreateAdminStage implements StageInterface, FormHandlerInterface, InjectContainerInterface
{
    private $yamlManager;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->yamlManager = new YamlDumper($this->container->get('kernel')->getRootDir() .'/config', 'custom_parameters.yml');
    }

    public function getName()
    {
        return 'createadmin';
    }

    public function getFormType()
    {
        return new CreateAdminType();
    }

    public function getTemplateName()
    {
        return "ZikulaCoreInstallerBundle:Install:createadmin.html.twig";
    }

    public function isNecessary()
    {
        $params = $this->yamlManager->getParameters();
        if (!empty($params['username']) && !empty($params['password']) && !empty($params['email'])) {
            $this->installSystemModules($params['lang']);
            $this->createAdmin($params);

            return false;
        }

        return true;
    }

    public function getTemplateParams()
    {
        return array();
    }

    public function handleFormResult(FormInterface $form)
    {
        $this->writeParams($form->getData());
        
        $this->installSystemModules($this->yamlManager->getParameter('lang'));
        $this->createAdmin($form->getData());

    }

    private function writeParams($data)
    {
        $params = array_merge($this->yamlManager->getParameters(), $data);
        try {
            $this->yamlManager->setParameters($params);
        } catch (IOException $e) {
            throw new AbortStageException(__f('Cannot write parameters to %s file.', 'custom_parameters.yml'));
        }
    }

    /**
     * This function inserts the admin's user data
     */
    private function createAdmin($params)
    {
        $em = $this->container->get('doctrine.entitymanager');

        // create the password hash
        $password = \UserUtil::getHashedPassword($params['password']);

        // prepare the data
        $username = mb_strtolower($params['username']);

        $nowUTC = new \DateTime(null, new \DateTimeZone('UTC'));
        $nowUTCStr = $nowUTC->format(UsersConstant::DATETIME_FORMAT);

        /** @var \Zikula\Module\UsersModule\Entity\UserEntity $entity */
        $entity = $em->find('ZikulaUsersModule:UserEntity', 2);
        $entity->setUname($username);
        $entity->setEmail($params['email']);
        $entity->setPass($password);
        $entity->setActivated(1);
        $entity->setUser_Regdate($nowUTCStr);
        $entity->setLastlogin($nowUTCStr);
        $em->persist($entity);

        $em->flush();
    }

    private function installSystemModules($lang = 'en')
    {
        // create a result set
        $results = array();

        $kernel = $this->container->get('kernel');

        $boot = new \Zikula\Bundle\CoreBundle\Bundle\Bootstrap();
        $helper = new \Zikula\Bundle\CoreBundle\Bundle\Helper\BootstrapHelper($boot->getConnection($kernel));
        $helper->createSchema();
        $helper->load();
        $bundles = array();
        // this neatly autoloads
        $boot->getPersistedBundles($kernel, $bundles);

        $systemModules = array(
            'ZikulaExtensionsModule',
            'ZikulaSettingsModule',
            'ZikulaThemeModule',
            'ZikulaAdminModule',
            'ZikulaPermissionsModule',
            'ZikulaGroupsModule',
            'ZikulaBlocksModule',
            'ZikulaUsersModule',
            'ZikulaSecurityCenterModule',
            'ZikulaCategoriesModule',
            'ZikulaMailerModule',
            'ZikulaSearchModule',
            'ZikulaRoutesModule',
        );

        // manually install the system modules
        foreach ($systemModules as $systemModule) {
            $className = null;
            $module = $kernel->getModule($systemModule);
            /** @var \Zikula\Core\AbstractModule $module */
            $className = $module->getInstallerClass();
            $bootstrap = $module->getPath().'/bootstrap.php';
            if (file_exists($bootstrap)) {
                include_once $bootstrap;
            }

            $instance = new $className($this->container, $module);
            if ($instance->install()) {
                $results[$systemModule] = true;
            }
        }

        // regenerate modules list
        $modApi = new \Zikula\Module\ExtensionsModule\Api\AdminApi($this->container, new \Zikula\Module\ExtensionsModule\ZikulaExtensionsModule());
        $modApi->regenerate(array('filemodules' => $modApi->getfilemodules()));

        // set each of the core modules to active
        reset($systemModules);
        foreach ($systemModules as $systemModule) {
            $mid = \ModUtil::getIdFromName($systemModule, true);
            $modApi->setstate(array('id' => $mid,
                'state' => \ModUtil::STATE_INACTIVE));
            $modApi->setstate(array('id' => $mid,
                'state' => \ModUtil::STATE_ACTIVE));
        }
        // Add them to the appropriate category
        reset($systemModules);
        $systemModulesCategories = array('ZikulaExtensionsModule' => __('System'),
            'ZikulaPermissionsModule' => __('Users'),
            'ZikulaGroupsModule' => __('Users'),
            'ZikulaBlocksModule' => __('Layout'),
            'ZikulaUsersModule' => __('Users'),
            'ZikulaThemeModule' => __('Layout'),
            'ZikulaSecurityCenterModule' => __('Security'),
            'ZikulaCategoriesModule' => __('Content'),
            'ZikulaMailerModule' => __('System'),
            'ZikulaSearchModule' => __('Content'),
            'ZikulaAdminModule' => __('System'),
            'ZikulaSettingsModule' => __('System'),
            'ZikulaRoutesModule' => __('System'),);

        $categories = \ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getall');
        $modulesCategories = array();
        foreach ($categories as $category) {
            $modulesCategories[$category['name']] = $category['cid'];
        }
        foreach ($systemModules as $systemModule) {
            $category = $systemModulesCategories[$systemModule];
            \ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'addmodtocategory',
                array('module' => $systemModule,
                    'category' => $modulesCategories[$category]));
        }
        // create the default blocks.
        $blockInstance = new \Zikula\Module\BlocksModule\BlocksModuleInstaller($this->container, $kernel->getModule('ZikulaBlocksModule'));
        $blockInstance->defaultdata();

        \System::setVar('language_i18n', $lang);

        return $results;
    }
}