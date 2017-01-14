<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Api;

use DataUtil;
use EventUtil;
use FileUtil;
use HookUtil;
use LogUtil;
use ModUtil;
use ReflectionClass;
use SecurityUtil;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use vierbergenlars\SemVer\expression;
use vierbergenlars\SemVer\version;
use Zikula;
use Zikula\Bundle\CoreBundle\Bundle\MetaData;
use Zikula\Bundle\CoreBundle\Bundle\Scanner;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Core\CoreEvents;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Event\ModuleStateEvent;
use Zikula\ExtensionsModule\Entity\ExtensionDependencyEntity;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Util as ExtensionsUtil;
use Zikula_AbstractVersion;
use ZLanguage;
use ZLoader;

/**
 * Administrative API functions for the Extensions module.
 * @deprecated remove at Core-2.0
 */
class AdminApi extends \Zikula_AbstractApi
{
    const EXTENSION_ENTITY = 'Zikula\ExtensionsModule\Entity\ExtensionEntity';

    /**
     * Update module information
     *
     * @param int[] $args {
     *      @type int $id The id number of the module
     *                     }
     *
     * @return array An associative array containing the module information for the specified module id
     */
    public function modify($args)
    {
        return $this->entityManager->getRepository(self::EXTENSION_ENTITY)->findOneBy($args);
    }

    /**
     * Update module information
     *
     * @param mixed[] $args {
     *      @type int    $id          The id number of the module to update
     *      @type string $displayname The new display name of the module
     *      @type string $description The new description of the module
     *      @type string $url         The url of the module
     *                       }
     *
     * @return boolean True on success, false on failure
     *
     * @throws \InvalidArgumentException Thrown if the id, displayname, description or url parameters are not set or empty or
     *                                          if the id is not numeric
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @throws \RuntimeException Thrown if the input module already exists
     */
    public function update($args)
    {
        // Argument check
        if (!isset($args['id']) || !is_numeric($args['id']) ||
                !isset($args['displayname']) ||
                !isset($args['description']) ||
                !isset($args['url'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', "::$args[id]", ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // check for duplicate display names
        // get the module info for the module being updated
        $moduleinforeal = ModUtil::getInfo($args['id']);
        // validate URL
        $moduleinfourl = ModUtil::getInfoFromName($args['url']);
        // If the two real module name don't match then the new display name can't be used
        if ($moduleinfourl && $moduleinfourl['name'] != $moduleinforeal['name']) {
            throw new \RuntimeException($this->__('Error! Could not save the module URL information. A duplicate module URL was detected.'));
        }

        if (empty($args['url'])) {
            throw new \InvalidArgumentException($this->__('Error! Module URL is a required field, please enter a unique name.'));
        }

        $path = realpath($this->getContainer()->get('kernel')->getRootDir(). '/../' . DataUtil::formatForOS($args['url']));
        if (is_dir($path)) {
            throw new \InvalidArgumentException($this->__('You have attempted to select an invalid name (it is a subdirectory).'));
        }

        if (empty($args['displayname'])) {
            throw new \InvalidArgumentException($this->__('Error! Display name is a required field, please enter a unique name.'));
        }

        // Rename operation
        /* @var ExtensionEntity $entity */
        $entity = $this->entityManager->getRepository(self::EXTENSION_ENTITY)->findOneBy(['id' => $args['id']]);
        $entity->setDisplayname($args['displayname']);
        $entity->setDescription($args['description']);
        $entity->setUrl($args['url']);

        $this->entityManager->persist($entity);

        // write changes to db
        $this->entityManager->flush();

        return true;
    }

    /**
     * Obtain a list of modules.
     *
     * @param mixed[] $args {
     *      @type int    $startnum The number of the module at which to start the list (for paging); optional, defaults to 1
     *      @type int    $numitems The number of the modules to return in the list (for paging); optional, defaults to
     *                                 -1, which returns modules starting at the specified number without limit
     *      @type int    $state    Filter the list by this state; optional
     *      @type int    $type     Filter the list by this type; optional
     *      @type string $letter   Filter the list by module names beginning with this letter; optional
     *                       }
     *
     * @return array An associative array of known modules
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function listmodules($args)
    {
        // Security check
        if (\ServiceUtil::getManager()->getParameter('installed')) {
            if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
                throw new AccessDeniedException();
            }
        }

        // create a QueryBuilder instance
        $qb = $this->entityManager->createQueryBuilder();

        // add select and from params
        $qb->select('e')
           ->from(self::EXTENSION_ENTITY, 'e');

        // filter by first letter of module
        if (isset($args['letter']) && !empty($args['letter'])) {
            $or = $qb->expr()->orX();
            $or->add($qb->expr()->like('e.name', ':letter1'));
            $or->add($qb->expr()->like('e.name', ':letter2'));
            $qb->andWhere($or)->setParameters(['letter1' => $args['letter'] . '%', 'letter2' => strtolower($args['letter']) . '%']);
        }

        // filter by type
        $type = (empty($args['type']) || $args['type'] < 0 || $args['type'] > ModUtil::TYPE_SYSTEM) ? 0 : (int)$args['type'];
        if ($type != 0) {
            $qb->andWhere($qb->expr()->eq('e.type', ':type'))->setParameter('type', $type);
        }

        // filter by module state
        if ($this->serviceManager['multisites']['enabled'] == 1) {
            $state = (empty($args['state']) || $args['state'] < -1 || $args['state'] > ModUtil::STATE_NOTALLOWED) ? 0 : (int)$args['state'];
        } else {
            $state = (empty($args['state']) || $args['state'] < -1 || $args['state'] > ModUtil::STATE_UPGRADED) ? 0 : (int)$args['state'];
        }
        switch ($state) {
            case ModUtil::STATE_UNINITIALISED:
            case ModUtil::STATE_INACTIVE:
            case ModUtil::STATE_ACTIVE:
            case ModUtil::STATE_MISSING:
            case ModUtil::STATE_UPGRADED:
            case ModUtil::STATE_NOTALLOWED:
            case ModUtil::STATE_INVALID:
                $qb->andWhere($qb->expr()->eq('e.state', $qb->expr()->literal($state))); // allowed 'literal' because var is validated
                break;

            case 10:
                $qb->andWhere($qb->expr()->gt('e.state', 10));
                break;
        }

        // add clause for ordering
        $sort = isset($args['sort']) ? (string)$args['sort'] : 'name';
        $sortdir = isset($args['sortdir']) && $args['sortdir'] ? $args['sortdir'] : 'ASC';
        $qb->orderBy('e.' . $sort, $sortdir);

        // add limit and offset
        $startnum = (!isset($args['startnum']) || empty($args['startnum']) || $args['startnum'] < 0) ? 0 : (int)$args['startnum'];
        $numitems = (!isset($args['numitems']) || empty($args['numitems']) || $args['numitems'] < 0) ? 0 : (int)$args['numitems'];
        if ($numitems > 0) {
            $qb->setFirstResult($startnum)
               ->setMaxResults($numitems);
        }

        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();

        // execute query
        $result = $query->getResult();

        return $result;
    }

    /**
     * Set the state of a module.
     *
     * @param int[] $args {
     *      @type int $id    The module id
     *      @type int $state The new state
     *                     }
     *
     * @return boolean True if successful, false otherwise
     *
     * @throws \InvalidArgumentException Thrown if either the id or state parameters are not set or numeric
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the module or
     *                                                                                 if the module cannot be obtained from the database
     * @throws \RuntimeException Thrown if the requested state transition is invalid
     */
    public function setState($args)
    {
        // Argument check
        if (!isset($args['id']) || !is_numeric($args['id']) ||
            !isset($args['state']) || !is_numeric($args['state'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // Security check
        if (\ServiceUtil::getManager()->getParameter('installed')) {
            if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_EDIT)) {
                throw new AccessDeniedException();
            }
        }

        // get module
        $module = $this->entityManager->getRepository(self::EXTENSION_ENTITY)->find($args['id']);
        if (empty($module)) {
            return false;
        }

        if ($module === false) {
            throw new AccessDeniedException();
        }

        // Check valid state transition
        switch ($args['state']) {
            case ModUtil::STATE_UNINITIALISED:
                if ($this->serviceManager['multisites']['enabled'] == 1) {
                    if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
                        throw new \RuntimeException($this->__('Error! Invalid module state transition.'));
                    }
                }
                break;
            case ModUtil::STATE_INACTIVE:
                $eventName = CoreEvents::MODULE_DISABLE;
                break;
            case ModUtil::STATE_ACTIVE:
                if ($module->getState() === ModUtil::STATE_INACTIVE) {
                    // ACTIVE is used for freshly installed modules, so only register the transition
                    // if previously inactive.
                    $eventName = CoreEvents::MODULE_ENABLE;
                }
                break;
            case ModUtil::STATE_MISSING:
                break;
            case ModUtil::STATE_UPGRADED:
                $oldstate = $module->getState();
                if ($oldstate == ModUtil::STATE_UNINITIALISED) {
                    throw new \RuntimeException($this->__('Error! Invalid module state transition.'));
                }
                break;
        }

        // change state
        $module->setState($args['state']);
        $this->entityManager->flush();

        // clear the cache before calling events
        /** @var $cacheClearer \Zikula\Bundle\CoreBundle\CacheClearer */
        $cacheClearer = $this->get('zikula.cache_clearer');
        $cacheClearer->clear('symfony.config');

        // state changed, so update the ModUtil::available-info for this module.
        $modinfo = ModUtil::getInfo($args['id']);
        ModUtil::available($modinfo['name'], true);

        if (isset($eventName)) {
            // only notify for enable or disable transitions
            $moduleBundle = \ModUtil::getModule($modinfo['name']);
            $event = new ModuleStateEvent($moduleBundle, ($moduleBundle === null) ? $modinfo : null);
            $this->getDispatcher()->dispatch($eventName, $event);
        }

        return true;
    }

    /**
     * Remove a module.
     *
     * @param mixed[] $args {
     *      @type int     $id                 The id of the module
     *      @type boolean $removedependents   Remove any modules dependent on this module (default: false) (not used!)
     *                       }
     *
     * @return boolean True on success, false on failure
     *
     * @throws \InvalidArgumentException Thrown if the id parameter is either not set or not numeric
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions over the module
     * @throws \RuntimeException Thrown if the module state cannot be changed or
     *                                  if the installer class isn't of the correct type
     */
    public function remove($args)
    {
        // Argument check
        if (!isset($args['id']) || !is_numeric($args['id'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        if (!isset($args['removedependents']) || !is_bool($args['removedependents'])) {
            $removedependents = false;
        } else {
            $removedependents = true;
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Get module information
        $modinfo = ModUtil::getInfo($args['id']);
        if (empty($modinfo)) {
            return false;
        }

        switch ($modinfo['state']) {
            case ModUtil::STATE_NOTALLOWED:
                throw new \RuntimeException($this->__f('Error! No permission to upgrade %s.', $modinfo['name']));
                break;
        }

        $osdir = DataUtil::formatForOS($modinfo['directory']);
        $modpath = ($modinfo['type'] == ModUtil::TYPE_SYSTEM) ? 'system' : 'modules';

        $oomod = ModUtil::isOO($modinfo['name']);

        // add autoloaders for 1.3-type modules
        if ($oomod && (false === strpos($osdir, '/')) && (is_dir("$modpath/$osdir/lib"))) {
            ZLoader::addAutoloader($osdir, [$modpath, "$modpath/$osdir/lib"]);
        }
        $module = ModUtil::getModule($modinfo['name'], true);
        $bootstrap = "$modpath/$osdir/bootstrap.php";
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }

        // Get module database info
        ModUtil::dbInfoLoad($modinfo['name'], $osdir);

        $version = ExtensionsUtil::getVersionMeta($modinfo['name'], $modpath, $module);
        if ($version instanceof MetaData) {
            // Core-2.0 Spec module
            $version = $this->get('zikula_hook_bundle.api.hook')->getHookContainerInstance($version);
        }

        // Module deletion function. Only execute if the module is initialised.
        if ($modinfo['state'] != ModUtil::STATE_UNINITIALISED) {
            $installer = $this->getInstaller($module, $modinfo);

            // perform the actual deletion of the module
            $func = [$installer, 'uninstall'];
            if (is_callable($func)) {
                if (call_user_func($func) != true) {
                    return false;
                }
            }
        }

        // Delete any module variables that the module cleanup function might have missed
        $query = $this->entityManager->createQueryBuilder()
                                     ->delete()
                                     ->from('Zikula\ExtensionsModule\Entity\ExtensionVarEntity', 'v')
                                     ->where('v.modname = :modname')
                                     ->setParameter('modname', $modinfo['name'])
                                     ->getQuery();
        $query->getResult();

        if (is_object($version)) {
            HookUtil::unregisterProviderBundles($version->getHookProviderBundles());
            HookUtil::unregisterSubscriberBundles($version->getHookSubscriberBundles());
            EventUtil::unregisterPersistentModuleHandlers($modinfo['name']);
        }

        // remove the entry from the modules table
        if ($this->serviceManager['multisites']['enabled'] == 1) {
            // who can access to the mainSite can delete the modules in any other site
            $canDelete = (($this->serviceManager['multisites.mainsiteurl'] == $this->request->query->get('sitedns', null)
                    && $this->serviceManager['multisites.based_on_domains'] == 0)
                || ($this->serviceManager['multisites.mainsiteurl'] == $_SERVER['HTTP_HOST']
                    && $this->serviceManager['multisites.based_on_domains'] == 1))
                ? 1 : 0;
            //delete the module infomation only if it is not allowed, missign or invalid
            if ($canDelete == 1 || $modinfo['state'] == ModUtil::STATE_NOTALLOWED || $modinfo['state'] == ModUtil::STATE_MISSING || $modinfo['state'] == ModUtil::STATE_INVALID) {
                // remove the entry from the modules table
                $query = $this->entityManager->createQueryBuilder()
                                             ->delete()
                                             ->from(self::EXTENSION_ENTITY, 'e')
                                             ->where('e.id = :id')
                                             ->setParameter('id', $args['id'])
                                             ->getQuery();
                $query->getResult();
            } else {
                //set state as uninitialised
                ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'setstate', ['id' => $args['id'], 'state' => ModUtil::STATE_UNINITIALISED]);
            }
        } else {
            // remove the entry from the modules table
            $query = $this->entityManager->createQueryBuilder()
                                         ->delete()
                                         ->from(self::EXTENSION_ENTITY, 'e')
                                         ->where('e.id = :id')
                                         ->setParameter('id', $args['id'])
                                         ->getQuery();
            $query->getResult();
        }

        // clear the cache before calling events
        /** @var $cacheClearer \Zikula\Bundle\CoreBundle\CacheClearer */
        $cacheClearer = $this->get('zikula.cache_clearer');
        $cacheClearer->clear('symfony.config');

        // remove in 1.5.0
        $event = new GenericEvent(null, $modinfo);
        $this->getDispatcher()->dispatch('installer.module.uninstalled', $event);

        $event = new ModuleStateEvent($module, ($module === null) ? $modinfo : null);
        $this->getDispatcher()->dispatch(CoreEvents::MODULE_REMOVE, $event);

        return true;
    }

    /**
     * Scan the file system for modules.
     *
     * This function scans the file system for modules and returns an array with all (potential) modules found.
     * This information is used to regenerate the module list.
     *
     * @param array $directories
     * @return array Thrown if the user doesn't have admin permissions over the module
     * @throws \Exception
     */
    public function getfilemodules(array $directories = [])
    {
        $directories = empty($directories) ? ['system', 'modules'] : $directories;
        // Security check
        if (\ServiceUtil::getManager()->getParameter('installed')) {
            if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
                throw new AccessDeniedException();
            }
        }

        $helper = $this->getContainer()->get('zikula_core.internal.bootstrap_helper');

        // sync the filesystem and the bundles table
        $helper->load();

        // Get all modules on filesystem
        $filemodules = [];

        $scanner = new Scanner();
        $scanner->scan($directories, 5);
        $newModules = $scanner->getModulesMetaData();

        // scan for all bundle-type modules (psr-0 & psr-4) in either /system or /modules
        /** @var MetaData $moduleMetaData */
        foreach ($newModules as $name => $moduleMetaData) {
            /* @todo psr-0 is deprecated - remove this in Core-2.0 */
            foreach ($moduleMetaData->getPsr0() as $ns => $path) {
                ZLoader::addPrefix($ns, $path);
            }

            foreach ($moduleMetaData->getPsr4() as $ns => $path) {
                ZLoader::addPrefixPsr4($ns, $path);
            }

            $bundleClass = $moduleMetaData->getClass();

            /** @var $bundle \Zikula\Core\AbstractModule */
            $bundle = new $bundleClass();
            $versionClass = $bundle->getVersionClass();

            if (class_exists($versionClass)) {
                // @todo 1.4-module spec - deprecated - remove in Core-2.0
                $version = new $versionClass($bundle);
                $version['name'] = $bundle->getName();

                $moduleVersionArray = $version->toArray();
                unset($moduleVersionArray['id']);
            } else {
                // 2.0-module spec
                $moduleMetaData->setTranslator($this->getContainer()->get('translator'));
                $moduleMetaData->setDirectoryFromBundle($bundle);
                $moduleVersionArray = $moduleMetaData->getFilteredVersionInfoArray();
            }

            // Work out if admin-capable
            // @deprecated - author must declare in Core 2.0
            // e.g. "capabilities": {"admin": {"route": "zikulafoomodule_admin_index"} }
            if (empty($moduleVersionArray['capabilities']['admin']) && file_exists($bundle->getPath().'/Controller/AdminController.php')) {
                $caps = $moduleVersionArray['capabilities'];
                $caps['admin'] = ['url' => ModUtil::url($bundle->getName(), 'admin', 'index')];
                $moduleVersionArray['capabilities'] = $caps;
            }

            // Work out if user-capable
            // @deprecated - author must declare in Core 2.0
            // e.g. "capabilities": {"user": {"route": "zikulafoomodule_user_index"} }
            if (empty($moduleVersionArray['capabilities']['user']) && file_exists($bundle->getPath().'/Controller/UserController.php')) {
                $caps = $moduleVersionArray['capabilities'];
                $caps['user'] = ['url' => ModUtil::url($bundle->getName(), 'user', 'index')];
                $moduleVersionArray['capabilities'] = $caps;
            }

            // loads the gettext domain for 3rd party modules
            if (!strpos($bundle->getPath(), 'modules') === false) {
                ZLanguage::bindModuleDomain($bundle->getName());
            }

            $moduleVersionArray['capabilities'] = serialize($moduleVersionArray['capabilities']);
            $moduleVersionArray['securityschema'] = serialize($moduleVersionArray['securityschema']);
            $moduleVersionArray['dependencies'] = serialize($moduleVersionArray['dependencies']);

            $filemodules[$bundle->getName()] = $moduleVersionArray;
            $filemodules[$bundle->getName()]['oldnames'] = isset($moduleVersionArray['oldnames']) ? $moduleVersionArray['oldnames'] : '';
        }

        /**
         * @deprecated All the legacy below is to be removed in Core 2.0
         */
        // set the paths to search
        $rootdirs = ['modules' => ModUtil::TYPE_MODULE]; // do not scan `/system` since all are accounted for above

        // scan for legacy modules
        // NOTE: the scan below does rescan all psr-0 & psr-4 type modules and intentionally fails.
        foreach ($rootdirs as $rootdir => $moduletype) {
            if (is_dir($rootdir)) {
                $dirs = FileUtil::getFiles($rootdir, false, true, null, 'd');

                foreach ($dirs as $dir) {
                    $oomod = false;
                    // register autoloader
                    if (file_exists("$rootdir/$dir/Version.php") || is_dir("$rootdir/$dir/lib")) {
                        ZLoader::addAutoloader($dir, [$rootdir, "$rootdir/$dir/lib"]);
                        ZLoader::addPrefix($dir, $rootdir);
                        $oomod = true;
                    }

                    // loads the gettext domain for 3rd party modules
                    if (is_dir("modules/$dir/locale")) {
                        ZLanguage::bindModuleDomain($dir);
                    }

                    try {
                        $modversion = ExtensionsUtil::getVersionMeta($dir, $rootdir);
                    } catch (\Exception $e) {
                        throw new \RuntimeException($e->getMessage());
                        continue;
                    }

                    if (!$modversion) {
                        continue;
                    }

                    if (!isset($modversion['capabilities'])) {
                        $modversion['capabilities'] = [];
                    }

                    $name = $dir;

                    // Get the module version
                    if (!$modversion instanceof Zikula_AbstractVersion) {
                        if (isset($modversion['profile']) && $modversion['profile']) {
                            $modversion['capabilities']['profile'] = '1.0';
                        }
                        if (isset($modversion['message']) && $modversion['message']) {
                            $modversion['capabilities']['message'] = '1.0';
                        }
                    } elseif ($oomod) {
                        // Work out if admin-capable
                        if (file_exists("$rootdir/$dir/lib/$dir/Controller/Admin.php")) {
                            $caps = $modversion['capabilities'];
                            $caps['admin'] = ['url' => ModUtil::url($modversion['name'], 'admin', 'index')];
                            $modversion['capabilities'] = $caps;
                        }

                        // Work out if user-capable
                        if (file_exists("$rootdir/$dir/lib/$dir/Controller/User.php")) {
                            $caps = $modversion['capabilities'];
                            $caps['user'] = ['url' => ModUtil::url($modversion['name'], 'user', 'index')];
                            $modversion['capabilities'] = $caps;
                        }
                    }

                    $version = $modversion['version'];
                    $description = $modversion['description'];

                    if (isset($modversion['displayname']) && !empty($modversion['displayname'])) {
                        $displayname = $modversion['displayname'];
                    } else {
                        $displayname = $modversion['name'];
                    }

                    $capabilities = serialize($modversion['capabilities']);

                    // bc for urls
                    if (isset($modversion['url']) && !empty($modversion['url'])) {
                        $url = $modversion['url'];
                    } else {
                        $url = $displayname;
                    }

                    if (isset($modversion['securityschema']) && is_array($modversion['securityschema'])) {
                        $securityschema = serialize($modversion['securityschema']);
                    } else {
                        $securityschema = serialize([]);
                    }

                    $core_min = isset($modversion['core_min']) ? $modversion['core_min'] : '';
                    $core_max = isset($modversion['core_max']) ? $modversion['core_max'] : '';
                    $oldnames = isset($modversion['oldnames']) ? $modversion['oldnames'] : '';

                    if (isset($modversion['dependencies']) && is_array($modversion['dependencies'])) {
                        $moddependencies = serialize($modversion['dependencies']);
                    } else {
                        $moddependencies = serialize([]);
                    }

                    $filemodules[$name] = [
                        'directory'       => $dir,
                        'name'            => $name,
                        'type'            => $moduletype,
                        'displayname'     => $displayname,
                        'url'             => $url,
                        'oldnames'        => $oldnames,
                        'version'         => $version,
                        'capabilities'    => $capabilities,
                        'description'     => $description,
                        'securityschema'  => $securityschema,
                        'dependencies'    => $moddependencies,
                        'core_min'        => $core_min,
                        'core_max'        => $core_max
                    ];

                    // important: unset modversion and modtype, otherwise the
                    // following modules will have some values not defined in
                    // the next version files to be read
                    unset($modversion);
                    unset($modtype);
                }
            }
        }

        return $filemodules;
    }

    /**
     * Regenerate modules list.
     *
     * @param array[] $args {
     *      @type array $filemodules An array of modules in the filesystem, as would be returned by
     *                                  {@link getfilemodules()}; optional, defaults to the results of $this->getfilemodules()
     *                       }
     *
     * @return boolean True on success, false on failure
     *
     * @throws \InvalidArgumentException Thrown if the filemodules parameter is either not set or not an array
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions over the module
     * @throws \RuntimeException Thrown if module information cannot be obtained from the database
     */
    public function regenerate($args)
    {
        // Security check
        if (\ServiceUtil::getManager()->getParameter('installed')) {
            if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
                throw new AccessDeniedException();
            }
        }

        $helper = $this->getContainer()->get('zikula_core.internal.bootstrap_helper');

        // sync the filesystem and the bundles table
        $helper->load();

        // Argument check
        if (!isset($args['filemodules']) || !is_array($args['filemodules'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // default action
        $filemodules = $args['filemodules'];
        $defaults = (isset($args['defaults']) ? $args['defaults'] : false);

        // Get all modules in DB
        $allmodules = $this->entityManager->getRepository(self::EXTENSION_ENTITY)->findAll();
        if (!$allmodules) {
            throw new \RuntimeException($this->__('Error! Could not load data.'));
        }

        // index modules by name
        $dbmodules = [];
        /* @var ExtensionEntity $module */
        foreach ($allmodules as $module) {
            $dbmodules[$module['name']] = $module->toArray();
        }

        // build a list of found modules and dependencies
        $fileModuleNames = [];
        $moddependencies = [];
        foreach ($filemodules as $modinfo) {
            $fileModuleNames[] = $modinfo['name'];
            if (isset($modinfo['dependencies']) && !empty($modinfo['dependencies'])) {
                $moddependencies[$modinfo['name']] = unserialize($modinfo['dependencies']);
            }
        }

        // see if any modules have changed name since last regeneration
        foreach ($filemodules as $name => $modinfo) {
            if (isset($modinfo['oldnames']) && !empty($modinfo['oldnames'])) {
                foreach ($dbmodules as $dbname => $dbmodinfo) {
                    if (isset($dbmodinfo['name']) && in_array($dbmodinfo['name'], (array)$modinfo['oldnames'])) {
                        // migrate its modvars
                        $query = $this->entityManager->createQueryBuilder()
                             ->update('Zikula\ExtensionsModule\Entity\ExtensionVarEntity', 'v')
                             ->set('v.modname', ':modname')
                             ->setParameter('modname', $modinfo['name'])
                             ->where('v.modname = :dbname')
                             ->setParameter('dbname', $dbname)
                             ->getQuery();
                        $query->execute();

                        // rename the module register
                        $query = $this->entityManager->createQueryBuilder()
                             ->update(self::EXTENSION_ENTITY, 'e')
                             ->set('e.name', ':modname')
                             ->setParameter('modname', $modinfo['name'])
                             ->where('e.id = :dbname')
                             ->setParameter('dbname', $dbmodules[$dbname]['id'])
                             ->getQuery();
                        $query->execute();

                        // replace the old module with the new one in the dbmodules array
                        $newmodule = $dbmodules[$dbname];
                        $newmodule['name'] = $modinfo['name'];
                        unset($dbmodules[$dbname]);
                        $dbname = $modinfo['name'];
                        $dbmodules[$dbname] = $newmodule;
                    }
                }
            }

            // If module was previously determined to be incompatible with the core. return to original state
            if (isset($dbmodules[$name]) && $dbmodules[$name]['state'] > 10) {
                $dbmodules[$name]['state'] = $dbmodules[$name]['state'] - ModUtil::INCOMPATIBLE_CORE_SHIFT;
                $this->setState(['id' => $dbmodules[$name]['id'], 'state' => $dbmodules[$name]['state']]);
            }

            // update the DB information for this module to reflect user settings (e.g. url)
            if (isset($dbmodules[$name]['id'])) {
                $modinfo['id'] = $dbmodules[$name]['id'];
                if ($dbmodules[$name]['state'] != ModUtil::STATE_UNINITIALISED && $dbmodules[$name]['state'] != ModUtil::STATE_INVALID) {
                    unset($modinfo['version']);
                }
                if (!$defaults) {
                    unset($modinfo['displayname']);
                    unset($modinfo['description']);
                    unset($modinfo['url']);
                }

                unset($modinfo['oldnames']);
                unset($modinfo['dependencies']);
                $modinfo['capabilities'] = unserialize($modinfo['capabilities']);
                $modinfo['securityschema'] = unserialize($modinfo['securityschema']);
                $module = $this->entityManager->getRepository(self::EXTENSION_ENTITY)->find($modinfo['id']);
                $module->merge($modinfo);
                $this->entityManager->flush();
            }

            // check core version is compatible with current
            $coreCompatibility = isset($filemodules[$name]['corecompatibility'])
                ? $filemodules[$name]['corecompatibility']
                : $this->formatCoreCompatibilityString($filemodules[$name]['core_min'], $filemodules[$name]['core_max']);
            $isCompatible = $this->isCoreCompatible($coreCompatibility);
            if (isset($dbmodules[$name])) {
                if (!$isCompatible) {
                    // module is incompatible with current core
                    $dbmodules[$name]['state'] = $dbmodules[$name]['state'] + ModUtil::INCOMPATIBLE_CORE_SHIFT;
                    $this->setState(['id' => $dbmodules[$name]['id'], 'state' => $dbmodules[$name]['state']]);
                }
                if (isset($dbmodules[$name]['state'])) {
                    $filemodules[$name]['state'] = $dbmodules[$name]['state'];
                }
            }
        }

        // See if we have lost any modules since last regeneration
        foreach ($dbmodules as $name => $modinfo) {
            if (!in_array($name, $fileModuleNames)) {
                $lostModule = $this->entityManager->getRepository(self::EXTENSION_ENTITY)->findOneBy(['name' => $name]);
                if (!$lostModule) {
                    throw new \RuntimeException($this->__f('Error! Could not load data for module %s.', [$name]));
                }
                $lostModuleState = $lostModule->getState();
                if (($lostModuleState == ModUtil::STATE_INVALID) || ($lostModuleState == ModUtil::STATE_INVALID + ModUtil::INCOMPATIBLE_CORE_SHIFT)) {
                    // module was invalid and subsequently removed from file system,
                    // or module was incompatible with core and subsequently removed, delete it
                    $this->entityManager->remove($lostModule);
                    $this->entityManager->flush();
                } elseif (($lostModuleState == ModUtil::STATE_UNINITIALISED) || ($lostModuleState == ModUtil::STATE_UNINITIALISED + ModUtil::INCOMPATIBLE_CORE_SHIFT)) {
                    // module was uninitialised and subsequently removed from file system, delete it
                    $this->entityManager->remove($lostModule);
                    $this->entityManager->flush();
                } else {
                    // Set state of module to 'missing'
                    $this->setState(['id' => $lostModule->getId(), 'state' => ModUtil::STATE_MISSING]);
                }

                unset($dbmodules[$name]);
            }
        }

        // See if we have gained any modules since last generation,
        // or if any current modules have been upgraded
        foreach ($filemodules as $name => $modinfo) {
            if (empty($dbmodules[$name])) {
                // set state to invalid if we can't determine an ID
                $modinfo['state'] = ModUtil::STATE_UNINITIALISED;
                if (!$modinfo['version']) {
                    $modinfo['state'] = ModUtil::STATE_INVALID;
                } else {
                    $coreCompatibility = isset($filemodules[$name]['corecompatibility'])
                        ? $filemodules[$name]['corecompatibility']
                        : $this->formatCoreCompatibilityString($filemodules[$name]['core_min'], $filemodules[$name]['core_max']);
                    // shift state if module is incompatible with core version
                    $modinfo['state'] = $this->isCoreCompatible($coreCompatibility) ? $modinfo['state'] : $modinfo['state'] + ModUtil::INCOMPATIBLE_CORE_SHIFT;
                }

                // unset some vars
                unset($modinfo['oldnames']);
                unset($modinfo['dependencies']);

                // unserialze some vars
                $modinfo['capabilities'] = unserialize($modinfo['capabilities']);
                $modinfo['securityschema'] = unserialize($modinfo['securityschema']);

                // insert new module to db
                if ($this->serviceManager['multisites']['enabled'] == 1) {
                    // only the main site can regenerate the modules list
                    if (($this->serviceManager['multisites.mainsiteurl'] == $this->request->query->get('sitedns', null)
                            && $this->serviceManager['multisites.based_on_domains'] == 0)
                        || ($this->serviceManager['multisites.mainsiteurl'] == $_SERVER['HTTP_HOST']
                            && $this->serviceManager['multisites.based_on_domains'] == 1)) {
                        $item = new ExtensionEntity();
                        $item->merge($modinfo);
                        $this->entityManager->persist($item);
                    }
                } else {
                    $item = new ExtensionEntity();
                    $item->merge($modinfo);
                    $this->entityManager->persist($item);
                }
                $this->entityManager->flush();
            } else {
                // module is in the db already
                if (($dbmodules[$name]['state'] == ModUtil::STATE_MISSING) || ($dbmodules[$name]['state'] == ModUtil::STATE_MISSING + ModUtil::INCOMPATIBLE_CORE_SHIFT)) {
                    // module was lost, now it is here again
                    $this->setState(['id' => $dbmodules[$name]['id'], 'state' => ModUtil::STATE_INACTIVE]);
                } elseif ((($dbmodules[$name]['state'] == ModUtil::STATE_INVALID)
                    || ($dbmodules[$name]['state'] == ModUtil::STATE_INVALID + ModUtil::INCOMPATIBLE_CORE_SHIFT))
                    && $modinfo['version']) {
                    $coreCompatibility = isset($filemodules[$name]['corecompatibility'])
                        ? $filemodules[$name]['corecompatibility']
                        : $this->formatCoreCompatibilityString($filemodules[$name]['core_min'], $filemodules[$name]['core_max']);
                    $isCompatible = $this->isCoreCompatible($coreCompatibility);
                    if ($isCompatible) {
                        // module was invalid, now it is valid
                        $item = $this->entityManager->getRepository(self::EXTENSION_ENTITY)->find($dbmodules[$name]['id']);
                        $item->setState(ModUtil::STATE_UNINITIALISED);
                        $this->entityManager->flush();
                    }
                }

                if ($dbmodules[$name]['version'] != $modinfo['version']) {
                    if ($dbmodules[$name]['state'] != ModUtil::STATE_UNINITIALISED &&
                            $dbmodules[$name]['state'] != ModUtil::STATE_INVALID) {
                        $this->setState(['id' => $dbmodules[$name]['id'], 'state' => ModUtil::STATE_UPGRADED]);
                    }
                }
            }
        }

        // now clear re-load the dependencies table with all current dependencies
        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();
        $connection->executeUpdate($platform->getTruncateTableSQL('module_deps', true));

        // loop round dependencies adding the module id - we do this now rather than
        // earlier since we won't have the id's for new modules at that stage
        ModUtil::flushCache();
        foreach ($moddependencies as $modname => $moddependency) {
            $modid = ModUtil::getIdFromName($modname);

            // each module may have multiple dependencies
            foreach ($moddependency as $dependency) {
                $dependency['modid'] = $modid;
                $item = new ExtensionDependencyEntity();
                $item->merge($dependency);
                $this->entityManager->persist($item);
            }
        }

        $this->entityManager->flush();

        return true;
    }

    /**
     * Initialise a module.
     *
     * @param mixed[] $args {
     *      @type int     $id               The module ID
     *                       }
     *
     * @return boolean|void True on success, false on failure, or null when we bypassed the installation
     *
     * @throws \InvalidArgumentException Thrown if the module id parameter is either not set or not numeric
     * @throws \RuntimeException Thrown if the module id isn't a valid module
     * @throws \RuntimeException Thrown if the module state prevents installation or if
     *                                  if the module isn't compatible with this version of Zikula or
     *                                  if the installer class isn't of the correct type or
     *                                  if the module state cannot be changed
     */
    public function initialise($args)
    {
        // Argument check
        if (!isset($args['id']) || !is_numeric($args['id'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // Get module information
        $modinfo = ModUtil::getInfo($args['id']);
        if (empty($modinfo)) {
            throw new \RuntimeException($this->__('Error! No such module ID exists.'));
        }

        switch ($modinfo['state']) {
            case ModUtil::STATE_NOTALLOWED:
                throw new \RuntimeException($this->__f('Error! No permission to install %s.', $modinfo['name']));
                break;
            default:
                if ($modinfo['state'] > 10) {
                    throw new \RuntimeException($this->__f('Error! %s is not compatible with this version of Zikula.', $modinfo['name']));
                }
        }

        $osdir = DataUtil::formatForOS($modinfo['directory']);
        ModUtil::dbInfoLoad($modinfo['name'], $osdir);
        $modpath = ($modinfo['type'] == ModUtil::TYPE_SYSTEM) ? 'system' : 'modules';

        // add autoloaders for 1.3-type modules
        if ((false === strpos($osdir, '/')) && (is_dir("$modpath/$osdir/lib"))) {
            ZLoader::addAutoloader($osdir, [$modpath, "$modpath/$osdir/lib"]);
        }
        $module = ModUtil::getModule($modinfo['name'], true);
        $bootstrap = "$modpath/$osdir/bootstrap.php";
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }

        $installer = $this->getInstaller($module, $modinfo);

        // perform the actual install of the module
        // system or module
        $func = [$installer, 'install'];
        if (is_callable($func)) {
            if (call_user_func($func) != true) {
                return false;
            }
        }

        // Update state of module
        if (!$this->setState(['id' => $args['id'], 'state' => ModUtil::STATE_ACTIVE])) {
            throw new \RuntimeException($this->__('Error! Could not change module state.'));
        }

        // clear the cache before calling events
        /** @var $cacheClearer \Zikula\Bundle\CoreBundle\CacheClearer */
        $cacheClearer = $this->get('zikula.cache_clearer');
        $cacheClearer->clear('symfony.config');

        // All went ok so issue installed event
        // remove this legacy in 1.5.0
        $event = new GenericEvent(null, $modinfo);
        $this->getDispatcher()->dispatch('installer.module.installed', $event);

        $event = new ModuleStateEvent($module, ($module === null) ? $modinfo : null);
        $this->getDispatcher()->dispatch(CoreEvents::MODULE_INSTALL, $event);

        // Success
        return true;
    }

    /**
     * Upgrade a module.
     *
     * @param mixed[] $args {
     *      @type int     $id                  The module ID
     *                       }
     *
     * @return boolean True on success, false on failure
     *
     * @throws \InvalidArgumentException Thrown if the module id parameter is either not set or not numeric
     * @throws \RuntimeException Thrown if the module id isn't a valid module
     * @throws \RuntimeException Thrown if the module state prevents upgrade or if
     *                                  if the module isn't compatible with this version of Zikula or
     *                                  if the installer class isn't of the correct type
     */
    public function upgrade($args)
    {
        // Argument check
        if (!isset($args['id']) || !is_numeric($args['id'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // Get module information
        $modinfo = ModUtil::getInfo($args['id']);
        if (empty($modinfo)) {
            throw new \RuntimeException($this->__('Error! No such module ID exists.'));
        }

        switch ($modinfo['state']) {
            case ModUtil::STATE_NOTALLOWED:
                throw new \RuntimeException($this->__f('Error! No permission to upgrade %s.', $modinfo['name']));
                break;
            default:
                if ($modinfo['state'] > 10) {
                    throw new \RuntimeException($this->__f('Error! %s is not compatible with this version of Zikula.', $modinfo['name']));
                }
        }

        $osdir = DataUtil::formatForOS($modinfo['directory']);
        ModUtil::dbInfoLoad($modinfo['name'], $osdir);
        $modpath = ($modinfo['type'] == ModUtil::TYPE_SYSTEM) ? 'system' : 'modules';

        // add autoloaders for 1.3-type modules
        if ((false === strpos($osdir, '/')) && (is_dir("$modpath/$osdir/lib"))) {
            ZLoader::addAutoloader($osdir, [$modpath, "$modpath/$osdir/lib"]);
        }
        $module = ModUtil::getModule($modinfo['name'], true);
        $bootstrap = "$modpath/$osdir/bootstrap.php";
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }

        $installer = $this->getInstaller($module, $modinfo);

        // perform the actual upgrade of the module
        $func = [$installer, 'upgrade'];

        if (is_callable($func)) {
            $result = call_user_func($func, $modinfo['version']);
            if (is_string($result)) {
                if ($result != $modinfo['version']) {
                    // update the last successful updated version
                    $item = $this->entityManager->getRepository(self::EXTENSION_ENTITY)->find($modinfo['id']);
                    $item['version'] = $result;
                    $this->entityManager->flush();
                }

                return false;
            } elseif ($result != true) {
                return false;
            }
        }
        $modversion['version'] = '0';

        $modversion = ExtensionsUtil::getVersionMeta($modinfo['name'], $modpath, $module);
        $version = $modversion['version'];

        // Update state of module
        $result = $this->setState(['id' => $args['id'], 'state' => ModUtil::STATE_ACTIVE]);
        if ($result) {
            LogUtil::registerStatus($this->__("Done! Module has been upgraded. Its status is now 'Active'."));
        } else {
            return false;
        }

        // update the module with the new version
        $item = $this->entityManager->getRepository(self::EXTENSION_ENTITY)->find($args['id']);
        $item['version'] = $version;
        $this->entityManager->flush();

        // clear the cache before calling events
        /** @var $cacheClearer \Zikula\Bundle\CoreBundle\CacheClearer */
        $cacheClearer = $this->get('zikula.cache_clearer');
        $cacheClearer->clear('symfony.config');

        if (\ServiceUtil::getManager()->getParameter('installed')) {
            // Upgrade succeeded, issue event.
            // remove this legacy in 1.5.0
            $event = new GenericEvent(null, $modinfo);
            $this->getDispatcher()->dispatch('installer.module.upgraded', $event);

            $event = new ModuleStateEvent($module, ($module === null) ? $modinfo : null);
            $this->getDispatcher()->dispatch(CoreEvents::MODULE_UPGRADE, $event);
        }
        // Success
        return true;
    }

    /**
     * Upgrade all modules.
     *
     * @return array An array of upgrade results, indexed by module name
     */
    public function upgradeall()
    {
        $upgradeResults = [];

        // regenerate modules list
        $filemodules = $this->getfilemodules();
        $this->regenerate(['filemodules' => $filemodules]);

        // get a list of modules needing upgrading
        $newmods = $this->listmodules(['state' => ModUtil::STATE_UPGRADED]);
        if (isset($newmods) && is_array($newmods) && !empty($newmods)) {
            // Sort upgrade order according to this list.
            $priorities = ['ZikulaExtensionsModule', 'ZikulaUsersModule', 'ZikulaZAuthModule', 'ZikulaGroupsModule', 'ZikulaPermissionsModule', 'ZikulaAdminModule', 'ZikulaBlocksModule', 'ZikulaThemeModule', 'ZikulaSettingsModule', 'ZikulaCategoriesModule', 'ZikulaSecurityCenterModule', 'ZikulaRoutesModule'];
            $sortedList = [];
            foreach ($priorities as $priority) {
                foreach ($newmods as $key => $modinfo) {
                    if ($modinfo['name'] == $priority) {
                        $sortedList[] = $modinfo;
                        unset($newmods[$key]);
                    }
                }
            }

            $newmods = array_merge($sortedList, $newmods);

            foreach ($newmods as $mod) {
                try {
                    $upgradeResults[$mod['name']] = $this->upgrade(['id' => $mod['id']]);
                } catch (\Exception $e) {
                    $upgradeResults[$mod['name']] = false;
                }
            }
        }

        return $upgradeResults;
    }

    /**
     * Utility function to count the number of items held by this module.
     *
     * @param mixed[] $args {
     *      @type string $letter Filter the count by the first letter of the module name; optional
     *      @type int    $state  Filter the count by the module state; optional
     *                       }
     *
     * @return integer The number of items held by this module
     */
    public function countitems($args)
    {
        // create a QueryBuilder instance
        $qb = $this->entityManager->createQueryBuilder();

        // add select and from params
        $qb->select('COUNT(e.id)')
           ->from(self::EXTENSION_ENTITY, 'e');

        // filter by first letter of module
        if (isset($args['letter']) && !empty($args['letter'])) {
            $or = $qb->expr()->orX();
            $or->add($qb->expr()->like('e.name', ':letter1'));
            $or->add($qb->expr()->like('e.name', ':letter2'));
            $qb->andWhere($or)->setParameters(['letter1' => $args['letter'] . '%', 'letter2' => strtolower($args['letter']) . '%']);
        }

        // filter by type
        $type = (empty($args['type']) || $args['type'] < 0 || $args['type'] > ModUtil::TYPE_SYSTEM) ? 0 : (int)$args['type'];
        if ($type != 0) {
            $qb->andWhere($qb->expr()->eq('e.type', ':type'))->setParameter('type', $type);
        }

        // filter by module state
        if ($this->serviceManager['multisites']['enabled'] == 1) {
            $state = (empty($args['state']) || $args['state'] < -1 || $args['state'] > ModUtil::STATE_NOTALLOWED) ? 0 : (int)$args['state'];
        } else {
            $state = (empty($args['state']) || $args['state'] < -1 || $args['state'] > ModUtil::STATE_UPGRADED) ? 0 : (int)$args['state'];
        }
        switch ($state) {
            case ModUtil::STATE_UNINITIALISED:
            case ModUtil::STATE_INACTIVE:
            case ModUtil::STATE_ACTIVE:
            case ModUtil::STATE_MISSING:
            case ModUtil::STATE_UPGRADED:
            case ModUtil::STATE_NOTALLOWED:
            case ModUtil::STATE_INVALID:
                $qb->andWhere($qb->expr()->eq('e.state', $qb->expr()->literal($state))); // allowed 'literal' because var is validated
                break;

            case 10:
                $qb->andWhere($qb->expr()->gt('e.state', 10));
                break;
        }

        $query = $qb->getQuery();

        $count = $query->getSingleScalarResult();

        return (int)$count;
    }

    /**
     * Get all module dependencies.
     *
     * @deprecated since 1.4.0 use getalldependencies instead
     * @todo remove in 1.5.0
     *
     * @see $this->getalldependencies instead
     *
     * @return array Array of dependencies
     */
    public function getdallependencies()
    {
        return $this->getalldependencies();
    }

    /**
     * Get all module dependencies.
     *
     * @return array Array of dependencies
     */
    public function getalldependencies()
    {
        $dependencies = $this->entityManager->getRepository('Zikula\ExtensionsModule\Entity\ExtensionDependencyEntity')->findBy([], ['modid' => 'ASC']);

        return $dependencies;
    }

    /**
     * Get dependencies for a module.
     *
     * @param int[] $args {
     *      @type int $modid Id of module to get dependencies for
     *                     }
     *
     * @return array|boolean Array of dependencies; false otherwise
     *
     * @throws \InvalidArgumentException Thrown if the modid paramter is not set, empty or not numeric
     */
    public function getdependencies($args)
    {
        // Argument check
        if (!isset($args['modid']) || empty($args['modid']) || !is_numeric($args['modid'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $dependencies = $this->entityManager->getRepository('Zikula\ExtensionsModule\Entity\ExtensionDependencyEntity')->findBy(['modid' => $args['modid']]);

        return $dependencies;
    }

    /**
     * Get dependents of a module.
     *
     * @param int[] $args {
     *      @type int $modid Id of module to get dependants for
     *                     }
     *
     * @return array|boolean Array of dependents; false otherwise
     *
     * @throws \InvalidArgumentException Thrown if the modid paramter is not set, empty or not numeric
     */
    public function getdependents($args)
    {
        // Argument check
        if (!isset($args['modid']) || empty($args['modid']) || !is_numeric($args['modid'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $modinfo = ModUtil::getInfo($args['modid']);

        $dependents = $this->entityManager->getRepository('Zikula\ExtensionsModule\Entity\ExtensionDependencyEntity')->findBy(['modname' => $modinfo['name']]);

        return $dependents;
    }

    /**
     * Check modules for consistency.
     *
     * @param array[] $args {
     *      @type array $filemodules Array of modules in the filesystem, as returned by {@link getfilemodules()}
     *                       }
     *
     * @see    getfilemodules()
     *
     * @return array An array of arrays with links to inconsistencies
     *
     * @throws \InvalidArgumentException Thrown if the filemodules parameter is either not set or not an array
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions over the module
     */
    public function checkconsistency($args)
    {
        // Security check
        if (\ServiceUtil::getManager()->getParameter('installed')) {
            if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
                throw new AccessDeniedException();
            }
        }

        // Argument check
        if (!isset($args['filemodules']) || !is_array($args['filemodules'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $filemodules = $args['filemodules'];

        $modulenames = [];
        $displaynames = [];

        $errors_modulenames = [];
        $errors_displaynames = [];

        // check for duplicate names or display names
        foreach ($filemodules as $dir => $modinfo) {
            if (isset($modulenames[strtolower($modinfo['name'])])) {
                $errors_modulenames[] = [
                    'name' => $modinfo['name'],
                    'dir1' => $modulenames[strtolower($modinfo['name'])],
                    'dir2' => $dir
                ];
            }

            if (isset($displaynames[strtolower($modinfo['displayname'])])) {
                $errors_displaynames[] = [
                    'name' => $modinfo['displayname'],
                    'dir1' => $displaynames[strtolower($modinfo['displayname'])],
                    'dir2' => $dir
                ];
            }

            if (isset($displaynames[strtolower($modinfo['url'])])) {
                $errors_displaynames[] = [
                    'name' => $modinfo['url'],
                    'dir1' => $displaynames[strtolower($modinfo['url'])],
                    'dir2' => $dir
                ];
            }

            $modulenames[strtolower($modinfo['name'])] = $dir;
            $displaynames[strtolower($modinfo['displayname'])] = $dir;
        }

        // do we need to check for duplicate oldnames as well?
        return [
            'errors_modulenames'  => $errors_modulenames,
            'errors_displaynames' => $errors_displaynames
        ];
    }

    /**
     * Check if a module comes from the core.
     *
     * @param string[] $args {
     *      @type string $modulename The name of the module to check.
     *                        }
     *
     * @return boolean True if it's a core module; otherwise false
     */
    public function iscoremodule($args)
    {
        // todo: get rid of this when we remove Forms
        if ($args['modulename'] === 'ZikulaPageLockModule') {
            return false;
        }

        return ModUtil::getModuleBaseDir($args['modulename']) === 'system' ? true : false;
    }

    /**
     * Determine if $min and $max values are compatible with Current Core version
     *
     * @param string $compatibilityString Semver
     * @return bool
     */
    private function isCoreCompatible($compatibilityString)
    {
        $coreVersion = new version(ZikulaKernel::VERSION);
        $requiredVersionExpression = new expression($compatibilityString);

        return $requiredVersionExpression->satisfiedBy($coreVersion);
    }

    /**
     * Format a compatibility string suitable for semver comparison using vierbergenlars/php-semver
     *
     * @param null $coreMin
     * @param null $coreMax
     * @return string
     */
    private function formatCoreCompatibilityString($coreMin = null, $coreMax = null)
    {
        $coreMin = !empty($coreMin) ? $coreMin : '1.4.0';
        $coreMax = !empty($coreMax) ? $coreMax : '2.9.99';

        return $coreMin . " - " . $coreMax;
    }

    /**
     * Get an instance of the module installer class
     * @param \Zikula\Core\AbstractModule|null $module
     * @param array $modInfo
     * @return \Zikula_AbstractInstaller|\Zikula\Core\ExtensionInstallerInterface
     */
    private function getInstaller($module, array $modInfo)
    {
        if (null === $module) {
            $className = ucwords($modInfo['name']) . '\\' . ucwords($modInfo['name']) . 'Installer';
            $classNameOld = ucwords($modInfo['name']) . '_Installer';
            $className = class_exists($className) ? $className : $classNameOld;
        } else {
            $className = $module->getInstallerClass();
        }
        $reflectionInstaller = new ReflectionClass($className);
        if ($reflectionInstaller->isSubclassOf('Zikula_AbstractInstaller')) {
            $installer = $reflectionInstaller->newInstanceArgs([$this->serviceManager, $module]);
        } elseif ($reflectionInstaller->isSubclassOf('\Zikula\Core\ExtensionInstallerInterface')) {
            $installer = $reflectionInstaller->newInstance();
            $installer->setBundle($module);
            if ($installer instanceof ContainerAwareInterface) {
                $installer->setContainer($this->getContainer());
            }
        } else {
            throw new \RuntimeException($this->__f("%s must be an instance of Zikula_AbstractInstaller or implement ExtensionInstallerInterface", $className));
        }

        return $installer;
    }
}
