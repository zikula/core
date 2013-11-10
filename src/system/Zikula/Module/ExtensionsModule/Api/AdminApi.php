<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\ExtensionsModule\Api;

use LogUtil;
use SecurityUtil;
use ModUtil;
use System;
use DataUtil;
use ZLoader;
use Zikula\Module\ExtensionsModule\Util as ExtensionsUtil;
use ZLanguage;
use ReflectionClass;
use SessionUtil;
use HookUtil;
use EventUtil;
use FormUtil;
use Zikula;
use FileUtil;
use Zikula_AbstractVersion;
use Zikula_Core;
use PluginUtil;
use Zikula\Core\Doctrine\Entity\ExtensionEntity;
use Zikula\Core\Doctrine\Entity\ExtensionDependencyEntity;
use Zikula\Bundle\CoreBundle\Bundle\Scanner;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Administrative API functions for the Extensions module.
 */
class AdminApi extends \Zikula_AbstractApi
{
    /**
     * Update module information
     *
     * @param int[] $args {<ul>
     *      <li>@type int $id The id number of the module</li>
     *                     </ul>}
     *
     * @return array An associative array containing the module information for the specified module id
     */
    public function modify($args)
    {
        return $this->entityManager->getRepository('Zikula\Core\Doctrine\Entity\ExtensionEntity')->findOneBy($args);
    }

    /**
     * Update module information
     *
     * @param mixed[] $args {<ul>
     *      <li>@type int    $id          The id number of the module to update</li>
     *      <li>@type string $displayname The new display name of the module</li>
     *      <li>@type string $description The new description of the module</li>
     *      <li>@type string $url         The url of the module</li>
     *                       </ul>}
     *
     * @return boolean True on success, false on failure
     *
     * @throws \InvalidArgumentException Thrown if the id, displayname, description or url parameters are not set or empty or
     *                                          if the id is not numeric
     * @throws AccessDeniedHttpException Thrown if the user doesn't have admin access to the module
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
            throw new AccessDeniedHttpException();
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

        if (empty($args['displayname'])) {
            throw new \InvalidArgumentException($this->__('Error! Display name is a required field, please enter a unique name.'));
        }

        // Rename operation
        /* @var ExtensionEntity $entity */
        $entity = $this->entityManager->getRepository('Zikula\Core\Doctrine\Entity\ExtensionEntity')->findOneBy(array('id' => $args['id']));
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
     * @param mixed[] $args {<ul>
     *      <li>@type int    $startnum The number of the module at which to start the list (for paging); optional, defaults to 1</li>
     *      <li>@type int    $numitems The number of the modules to return in the list (for paging); optional, defaults to
     *                                 -1, which returns modules starting at the specified number without limit</li>
     *      <li>@type int    $state    Filter the list by this state; optional</li>
     *      <li>@type int    $type     Filter the list by this type; optional</li>
     *      <li>@type string $letter   Filter the list by module names beginning with this letter; optional</li>
     *                       </ul>}
     *
     * @return array An associative array of known modules
     *
     * @throws AccessDeniedHttpException Thrown if the user doesn't have admin access to the module
     */
    public function listmodules($args)
    {
        // Security check
        if (!System::isInstalling()) {
            if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
                throw new AccessDeniedHttpException();
            }
        }

        // create a QueryBuilder instance
        $qb = $this->entityManager->createQueryBuilder();

        // add select and from params
        $qb->select('e')
           ->from('Zikula\Core\Doctrine\Entity\ExtensionEntity', 'e');

        // filter by first letter of module
        if (isset($args['letter']) && !empty($args['letter'])) {
            $clause1 = $qb->expr()->like('e.name', $qb->expr()->literal($args['letter'] . '%'));
            $clause2 = $qb->expr()->like('e.name', $qb->expr()->literal(strtolower($args['letter']) . '%'));
            $qb->andWhere($clause1 . ' OR ' . $clause2);
        }

        // filter by type
        $type = (empty($args['type']) || $args['type'] < 0 || $args['type'] > ModUtil::TYPE_SYSTEM) ? 0 : (int)$args['type'];
        if ($type != 0) {
            $qb->andWhere($qb->expr()->eq('e.type', $qb->expr()->literal($type)));
        }

        // filter by module state
        if ($this->serviceManager['multisites.enabled'] == 1) {
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
                $qb->andWhere($qb->expr()->eq('e.state', $qb->expr()->literal($state)));
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
     * @param int[] $args {<ul>
     *      <li>@type int $id    The module id</li>
     *      <li>@type int $state The new state</li>
     *                     </ul>}
     *
     * @return boolean True if successful, false otherwise
     *
     * @throws \InvalidArgumentException Thrown if either the id or state parameters are not set or numeric
     * @throws AccessDeniedHttpException Thrown if the user doesn't have edit permissions over the module or
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
        if (!System::isInstalling()) {
            if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_EDIT)) {
                throw new AccessDeniedHttpException();
            }
        }

        // get module
        $module = $this->entityManager->getRepository('Zikula\Core\Doctrine\Entity\ExtensionEntity')->find($args['id']);
        if (empty($module)) {
            return false;
        }

        if ($module === false) {
            throw new AccessDeniedHttpException();
        }

        // Check valid state transition
        switch ($args['state']) {
            case ModUtil::STATE_UNINITIALISED:
                if ($this->serviceManager['multisites.enabled'] == 1) {
                    if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
                        throw new \RuntimeException($this->__('Error! Invalid module state transition.'));
                    }
                }
                break;
            case ModUtil::STATE_INACTIVE:
                break;
            case ModUtil::STATE_ACTIVE:
                break;
            case ModUtil::STATE_MISSING:
                break;
            case ModUtil::STATE_UPGRADED:
                $oldstate = $module['state'];
                if ($oldstate == ModUtil::STATE_UNINITIALISED) {
                    throw new \RuntimeException($this->__('Error! Invalid module state transition.'));
                }
                break;
        }

        // change state
        $module['state'] = $args['state'];
        $this->entityManager->flush();

        // state changed, so update the ModUtil::available-info for this module.
        $modinfo = ModUtil::getInfo($args['id']);
        ModUtil::available($modinfo['name'], true);

        return true;
    }

    /**
     * Remove a module.
     *
     * @param mixed[] $args {<ul>
     *      <li>@type int     $id                 The id of the module</li>
     *      <li>@type boolean $removedependents   Remove any modules dependent on this module (default: false)</li>
     *      <li>@type boolean $interactive_remove Whether to operat in interactive mode or not</li>
     *                       </ul>}
     *
     * @return boolean True on success, false on failure
     *
     * @throws \InvalidArgumentException Thrown if the id parameter is either not set or not numeric
     * @throws AccessDeniedHttpException Thrown if the user doesn't have admin permissions over the module
     * @throws NotFoundHttpException Thrown if requested module id isn't a valid module
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
            throw new AccessDeniedHttpException();
        }

        // Get module information
        $modinfo = ModUtil::getInfo($args['id']);
        if (empty($modinfo)) {
            throw new NotFoundHttpException($this->__('Error! No such module ID exists.'));
        }

        switch ($modinfo['state']) {
            case ModUtil::STATE_NOTALLOWED:
                throw new \RuntimeException($this->__f('Error! No permission to upgrade %s.', $modinfo['name']));
                break;
        }

        $osdir = DataUtil::formatForOS($modinfo['directory']);
        $modpath = ($modinfo['type'] == ModUtil::TYPE_SYSTEM) ? 'system' : 'modules';

        $oomod = ModUtil::isOO($modinfo['name']);

        if ($oomod && false === strpos($osdir, '/')) {
            ZLoader::addAutoloader($osdir, array($modpath, "$modpath/$osdir/lib"));
        }

        $version = ExtensionsUtil::getVersionMeta($modinfo['name'], $modpath);

        $bootstrap = "$modpath/$osdir/bootstrap.php";
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }

        if ($modinfo['type'] == ModUtil::TYPE_MODULE) {
            if (is_dir("modules/$osdir/Resources/locale") || is_dir("modules/$osdir/locale")) {
                ZLanguage::bindModuleDomain($modinfo['name']);
            }
        }

        // Get module database info
        ModUtil::dbInfoLoad($modinfo['name'], $osdir);

        // Module deletion function. Only execute if the module is initialised.
        if ($modinfo['state'] != ModUtil::STATE_UNINITIALISED) {
            $module = ModUtil::getModule($modinfo['name']);
            if (null === $module) {
                $className = ucwords($modinfo['name']).'\\'.ucwords($modinfo['name']).'Installer';
                $classNameOld = ucwords($modinfo['name']) . '_Installer';
                $className = class_exists($className) ? $className : $classNameOld;
            } else {
                $className = $module->getInstallerClass();
            }
            $reflectionInstaller = new ReflectionClass($className);
            if (!$reflectionInstaller->isSubclassOf('Zikula_AbstractInstaller')) {
                throw new \RuntimeException($this->__f("%s must be an instance of Zikula_AbstractInstaller", $className));
            }
            $installer = $reflectionInstaller->newInstanceArgs(array($this->serviceManager, $module));

            // perform the actual deletion of the module
            $func = array($installer, 'uninstall');
            if (is_callable($func)) {
                if (call_user_func($func) != true) {
                    return false;
                }
            }
        }

        // Delete any module variables that the module cleanup function might have missed
        $query = $this->entityManager->createQueryBuilder()
                                     ->delete()
                                     ->from('Zikula\Core\Doctrine\Entity\ExtensionVarEntity', 'v')
                                     ->where('v.modname = :modname')
                                     ->setParameter('modname', $modinfo['name'])
                                     ->getQuery();
        $query->getResult();

        HookUtil::unregisterProviderBundles($version->getHookProviderBundles());
        HookUtil::unregisterSubscriberBundles($version->getHookSubscriberBundles());
        EventUtil::unregisterPersistentModuleHandlers($modinfo['name']);

        // remove the entry from the modules table
        if ($this->serviceManager['multisites.enabled'] == 1) {
            // who can access to the mainSite can delete the modules in any other site
            $canDelete = (($this->serviceManager['multisites.mainsiteurl'] == $this->request->query->get('sitedns', null) && $this->serviceManager['multisites.based_on_domains'] == 0) || ($this->serviceManager['multisites.mainsiteurl'] == $_SERVER['HTTP_HOST'] && $this->serviceManager['multisites.based_on_domains'] == 1)) ? 1 : 0;
            //delete the module infomation only if it is not allowed, missign or invalid
            if ($canDelete == 1 || $modinfo['state'] == ModUtil::STATE_NOTALLOWED || $modinfo['state'] == ModUtil::STATE_MISSING || $modinfo['state'] == ModUtil::STATE_INVALID) {
                // remove the entry from the modules table
                $query = $this->entityManager->createQueryBuilder()
                                             ->delete()
                                             ->from('Zikula\Core\Doctrine\Entity\ExtensionEntity', 'e')
                                             ->where('e.id = :id')
                                             ->setParameter('id', $args['id'])
                                             ->getQuery();
                $query->getResult();
            } else {
                //set state as uninnitialised
                ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'setstate', array('id' => $args['id'], 'state' => ModUtil::STATE_UNINITIALISED));
            }
        } else {
            // remove the entry from the modules table
            $query = $this->entityManager->createQueryBuilder()
                                         ->delete()
                                         ->from('Zikula\Core\Doctrine\Entity\ExtensionEntity', 'e')
                                         ->where('e.id = :id')
                                         ->setParameter('id', $args['id'])
                                         ->getQuery();
            $query->getResult();
        }

        $event = new \Zikula\Core\Event\GenericEvent(null, $modinfo);
        $this->getDispatcher()->dispatch('installer.module.uninstalled', $event);

        return true;
    }

    /**
     * Scan the file system for modules.
     *
     * This function scans the file system for modules and returns an array with all (potential) modules found.
     * This information is used to regenerate the module list.
     *
     * @throws AccessDeniedHttpException Thrown if the user doesn't have admin permissions over the module
     * @throws \RuntimeException Thrown if the version information of a module cannot be found
     *
     * @return array An array of modules found in the file system.
     */
    public function getfilemodules()
    {
        // Security check
        if (!System::isInstalling()) {
            if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
                throw new AccessDeniedHttpException();
            }
        }

        $boot = new \Zikula\Bundle\CoreBundle\Bundle\Bootstrap();
        $helper = new \Zikula\Bundle\CoreBundle\Bundle\Helper\BootstrapHelper($boot->getConnection($this->getContainer()->get('kernel')));

        $helper->load();

        // Get all modules on filesystem
        $filemodules = array();

        $scanner = new Scanner();
        $scanner->scan(array('system', 'modules'), 5);
        $newModules = $scanner->getModulesMetaData();

        foreach ($newModules as $name => $module) {
            foreach ($module->getPsr0() as $ns => $path) {
                ZLoader::addPrefix($ns, $path);
            }
            $class = $module->getClass();

            /** @var $bundle \Zikula\Core\AbstractModule */
            $bundle = new $class;
            $class = $bundle->getVersionClass();

            $version = new $class($bundle);
            $version['name'] = $bundle->getName();

            $array = $version->toArray();
            unset($array['id']);

            // Work out if admin-capable
            if (file_exists($bundle->getPath().'/Controller/AdminController.php')) {
                $caps = $array['capabilities'];
                $caps['admin'] = array('version' => '1.0');
                $array['capabilities'] = $caps;
            }

            // Work out if user-capable
            if (file_exists($bundle->getPath().'/Controller/UserController.php')) {
                $caps = $array['capabilities'];
                $caps['user'] = array('version' => '1.0');
                $array['capabilities'] = $caps;
            }

            $array['capabilities'] = serialize($array['capabilities']);
            $array['securityschema'] = serialize($array['securityschema']);
            $array['dependencies'] = serialize($array['dependencies']);

            $filemodules[$bundle->getName()] = $array;
            $filemodules[$bundle->getName()]['oldnames'] = serialize(array());
        }

        // set the paths to search
        $rootdirs = array('system' => ModUtil::TYPE_SYSTEM, 'modules' => ModUtil::TYPE_MODULE);

        foreach ($rootdirs as $rootdir => $moduletype) {
            if (is_dir($rootdir)) {
                $dirs = FileUtil::getFiles($rootdir, false, true, null, 'd');

                foreach ($dirs as $dir) {
                    $oomod = false;
                    // register autoloader
                    if (file_exists("$rootdir/$dir/{$dir}Version.php") || file_exists("$rootdir/$dir/Version.php") || is_dir("$rootdir/$dir/lib")) {
                        ZLoader::addAutoloader($dir, array($rootdir, "$rootdir/$dir/lib"));
                        ZLoader::addPrefix($dir, $rootdir);
                        $oomod = true;
                    }

                    // loads the gettext domain for 3rd party modules
                    if ($rootdir == 'modules' && (is_dir("modules/$dir/Resources/locale") || is_dir("modules/$dir/locale"))) {
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
                        $modversion['capabilities'] = array();
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
                        // Work out if admin-capable
                        if (file_exists("$rootdir/$dir/pnadmin.php") || is_dir("$rootdir/$dir/pnadmin")) {
                            $modversion['capabilities']['admin'] = '1.0';
                        }

                        // Work out if user-capable
                        if (file_exists("$rootdir/$dir/pnuser.php") || is_dir("$rootdir/$dir/pnuser")) {
                            $modversion['capabilities']['user'] = '1.0';
                        }
                    } elseif ($oomod) {
                        // Work out if admin-capable
                        if (file_exists("$rootdir/$dir/Controller/AdminController.php") || file_exists("$rootdir/$dir/Controller/Admin.php") || file_exists("$rootdir/$dir/lib/$dir/Controller/Admin.php")) {
                            $caps = $modversion['capabilities'];
                            $caps['admin'] = array('version' => '1.0');
                            $modversion['capabilities'] = $caps;
                        }

                        // Work out if user-capable
                        if (file_exists("$rootdir/$dir/Controller/UserController.php") || file_exists("$rootdir/$dir/Controller/User.php") || file_exists("$rootdir/$dir/lib/$dir/Controller/User.php")) {
                            $caps = $modversion['capabilities'];
                            $caps['user'] = array('version' => '1.0');
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
                        $securityschema = serialize(array());
                    }

                    $core_min = isset($modversion['core_min']) ? $modversion['core_min'] : '';
                    $core_max = isset($modversion['core_max']) ? $modversion['core_max'] : '';
                    $oldnames = isset($modversion['oldnames']) ? $modversion['oldnames'] : '';

                    if (isset($modversion['dependencies']) && is_array($modversion['dependencies'])) {
                        $moddependencies = serialize($modversion['dependencies']);
                    } else {
                        $moddependencies = serialize(array());
                    }

                    $filemodules[$name] = array(
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
                        'core_max'        => $core_max,
                    );

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
     * @param array[] $args {<ul>
     *      <li>@type array $filemodules An array of modules in the filesystem, as would be returned by
     *                                  {@link getfilemodules()}; optional, defaults to the results of $this->getfilemodules()</li>
     *                       </ul>}
     *
     * @return boolean True on success, false on failure
     *
     * @throws \InvalidArgumentException Thrown if the filemodules parameter is either not set or not an array
     * @throws AccessDeniedHttpException Thrown if the user doesn't have admin permissions over the module
     * @throws NotFoundHttpException Thrown if module information cannot be obtained from the database
     */
    public function regenerate($args)
    {
        // Security check
        if (!System::isInstalling()) {
            if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
                throw new AccessDeniedHttpException();
            }
        }

        $boot = new \Zikula\Bundle\CoreBundle\Bundle\Bootstrap();
        $helper = new \Zikula\Bundle\CoreBundle\Bundle\Helper\BootstrapHelper($boot->getConnection($this->getContainer()->get('kernel')));

        $helper->load();

        // Argument check
        if (!isset($args['filemodules']) || !is_array($args['filemodules'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $entity = 'Zikula\Core\Doctrine\Entity\ExtensionEntity';

        // default action
        $filemodules = $args['filemodules'];
        $defaults = (isset($args['defaults']) ? $args['defaults'] : false);

        // Get all modules in DB
        $allmodules = $this->entityManager->getRepository('Zikula\Core\Doctrine\Entity\ExtensionEntity')->findAll();
        if (!$allmodules) {
            throw new NotFoundHttpException($this->__('Error! Could not load data.'));
        }

        // index modules by name
        $dbmodules = array();
        /* @var ExtensionEntity $module */
        foreach ($allmodules as $module) {
            $dbmodules[$module['name']] = $module->toArray();
        }

        // build a list of found modules and dependencies
        $module_names = array();
        $moddependencies = array();
        foreach ($filemodules as $modinfo) {
            $module_names[] = $modinfo['name'];
            if (isset($modinfo['dependencies']) && !empty($modinfo['dependencies'])) {
                $moddependencies[$modinfo['name']] = unserialize($modinfo['dependencies']);
            }
        }

        // see if any modules have changed name since last generation
        foreach ($filemodules as $name => $modinfo) {
            if (isset($modinfo['oldnames']) && !empty($modinfo['oldnames'])) {
                foreach ($dbmodules as $dbname => $dbmodinfo) {
                    if (isset($dbmodinfo['name']) && in_array($dbmodinfo['name'], (array)$modinfo['oldnames'])) {
                        // migrate its modvars
                        $query = $this->entityManager->createQueryBuilder()
                                                     ->update('UPDATE Zikula\Core\Doctrine\Entity\ExtensionVarEntity', 'v')
                                                     ->set('v.modname', $modinfo['name'])
                                                     ->where('v.modname = :dbname')
                                                     ->setParameter('dbanme', $dbname)
                                                     ->getQuery();
                        $query->getResult();

                        // rename the module register
                        $query = $this->entityManager->createQueryBuilder()
                                                     ->update('Zikula\Core\Doctrine\Entity\ExtensionEntity', 'e')
                                                     ->set('e.name', $modinfo['name'])
                                                     ->where('e.id = :dbname')
                                                     ->setParameter('dbanme', $dbmodules[$dbname]['id'])
                                                     ->getQuery();
                        $query->getResult();

                        // replace the old module with the new one in the dbmodules array
                        $newmodule = $dbmodules[$dbname];
                        $newmodule['name'] = $modinfo['name'];
                        unset($dbmodules[$dbname]);
                        $dbname = $modinfo['name'];
                        $dbmodules[$dbname] = $newmodule;
                    }
                }
            }

            if (isset($dbmodules[$name]) && $dbmodules[$name]['state'] > 10) {
                $dbmodules[$name]['state'] = $dbmodules[$name]['state'] - 20;
                $this->setState(array('id' => $dbmodules[$name]['id'], 'state' => $dbmodules[$name]['state']));
            }

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
                $module = $this->entityManager->getRepository($entity)->find($modinfo['id']);
                $module->merge($modinfo);
                $this->entityManager->flush();
            }

            // check core version is compatible with current
            $minok = 0;
            $maxok = 0;
            // strip any -dev, -rcN etc from version number
            $coreVersion = preg_replace('#(\d+\.\d+\.\d+).*#', '$1', Zikula_Core::VERSION_NUM);
            if (!empty($filemodules[$name]['core_min'])) {
                $minok = version_compare($coreVersion, $filemodules[$name]['core_min']);
            }
            if (!empty($filemodules[$name]['core_max'])) {
                $maxok = version_compare($filemodules[$name]['core_max'], $coreVersion);
            }

            if (isset($dbmodules[$name])) {
                if ($minok == -1 || $maxok == -1) {
                    $dbmodules[$name]['state'] = $dbmodules[$name]['state'] + 20;
                    $this->setState(array('id' => $dbmodules[$name]['id'], 'state' => $dbmodules[$name]['state']));
                }
                if (isset($dbmodules[$name]['state'])) {
                    $filemodules[$name]['state'] = $dbmodules[$name]['state'];
                }
            }
        }

        // See if we have lost any modules since last generation
        foreach ($dbmodules as $name => $modinfo) {
            if (!in_array($name, $module_names)) {
                $lostmodule = $this->entityManager->getRepository($entity)->findOneBy(array('name' => $name));
                if (!$lostmodule) {
                    throw new NotFoundHttpException($this->__f('Error! Could not load data for module %s.', array($name)));
                }

                if ($dbmodules[$name]['state'] == ModUtil::STATE_INVALID) {
                    // module was invalid and now it was removed, delete it
                    $this->remove(array('id' => $dbmodules[$name]['id']));
                } elseif ($dbmodules[$name]['state'] == ModUtil::STATE_UNINITIALISED) {
                    // module was uninitialised and subsequently removed, delete it
                    $this->remove(array('id' => $dbmodules[$name]['id']));
                } else {
                    // Set state of module to 'missing'
                    $this->setState(array('id' => $dbmodules[$name]['id'], 'state' => ModUtil::STATE_MISSING));
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
                    // check if module is compatible with core version
                    $minok = 0;
                    $maxok = 0;
                    // strip any -dev, -rcN etc from version number
                    $coreVersion = preg_replace('#(\d+\.\d+\.\d+).*#', '$1', Zikula_Core::VERSION_NUM);
                    if (!empty($modinfo['core_min'])) {
                        $minok = version_compare($coreVersion, $modinfo['core_min']);
                    }
                    if (!empty($modinfo['core_max'])) {
                        $maxok = version_compare($modinfo['core_max'], $coreVersion);
                    }
                    if ($minok == -1 || $maxok == -1) {
                        $modinfo['state'] = ModUtil::STATE_NOTALLOWED;
                    }
                }

                // unset some vars
                unset($modinfo['oldnames']);
                unset($modinfo['dependencies']);

                // unserialze some vars
                $modinfo['capabilities'] = unserialize($modinfo['capabilities']);
                $modinfo['securityschema'] = unserialize($modinfo['securityschema']);

                // insert new module to db
                if ($this->serviceManager['multisites.enabled'] == 1) {
                    // only the main site can regenerate the modules list
                    if (($this->serviceManager['multisites.mainsiteurl'] == FormUtil::getPassedValue('sitedns', null, 'GET') && $this->serviceManager['multisites.based_on_domains'] == 0) || ($this->serviceManager['multisites.mainsiteurl'] == $_SERVER['HTTP_HOST'] && $this->serviceManager['multisites.based_on_domains'] == 1)) {
                        $item = new $entity;
                        $item->merge($modinfo);
                        $this->entityManager->persist($item);
                    }
                } else {
                    $item = new $entity;
                    $item->merge($modinfo);
                    $this->entityManager->persist($item);
                }

                $this->entityManager->flush();
            } else {
                // module is in the db already
                if ($dbmodules[$name]['state'] == ModUtil::STATE_MISSING) {
                    // module was lost, now it is here again
                    $this->setState(array('id' => $dbmodules[$name]['id'], 'state' => ModUtil::STATE_INACTIVE));
                } elseif ($dbmodules[$name]['state'] == ModUtil::STATE_INVALID && $modinfo['version']) {
                    // module was invalid, now it is valid
                    $item = $this->entityManager->getRepository($entity)->find($dbmodules[$name]['id']);
                    $item['state'] = ModUtil::STATE_UNINITIALISED;
                    $this->entityManager->flush();
                }

                if ($dbmodules[$name]['version'] != $modinfo['version']) {
                    if ($dbmodules[$name]['state'] != ModUtil::STATE_UNINITIALISED &&
                            $dbmodules[$name]['state'] != ModUtil::STATE_INVALID) {
                        $this->setState(array('id' => $dbmodules[$name]['id'], 'state' => ModUtil::STATE_UPGRADED));
                    }
                }
            }
        }

        // now clear re-load the dependencies table with all current dependencies
        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();
        $connection->executeUpdate($platform->getTruncateTableSQL('module_deps', true));

        // loop round dependences adding the module id - we do this now rather than
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
     * @param mixed[] $args {<ul>
     *      <li>@type int     $id               The module ID</li>
     *      <li>@type boolean $interactive_mode Perform the initialization in interactive mode or not</li>
     *                       </ul>}
     *
     * @return boolean|void True on success, false on failure, or null when we bypassed the installation
     *
     * @throws \InvalidArgumentException Thrown if the module id parameter is either not set or not numeric
     * @throws NotFoundHttpException Thrown if the module id isn't a valid module
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
            throw new NotFoundHttpException($this->__('Error! No such module ID exists.'));
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

        // Get module database info
        $osdir = DataUtil::formatForOS($modinfo['directory']);
        ModUtil::dbInfoLoad($modinfo['name'], $osdir);
        $modpath = ($modinfo['type'] == ModUtil::TYPE_SYSTEM) ? 'system' : 'modules';

        // load module maintainence functions
        if (false === strpos($osdir, '/')) {
            ZLoader::addAutoloader($osdir, array($modpath, "$modpath/$osdir/lib"));
        }

        $bootstrap = "$modpath/$osdir/bootstrap.php";
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }

        if ($modinfo['type'] == ModUtil::TYPE_MODULE) {
            if (is_dir("modules/$osdir/Resources/locale") || is_dir("modules/$osdir/locale")) {
                ZLanguage::bindModuleDomain($modinfo['name']);
            }
        }

        $module = ModUtil::getModule($modinfo['name']);
        if (null === $module) {
            $className = ucwords($modinfo['name']).'\\'.ucwords($modinfo['name']).'Installer';
            $classNameOld = ucwords($modinfo['name']) . '_Installer';
            $className = class_exists($className) ? $className : $classNameOld;
        } else {
            $className = $module->getInstallerClass();
        }
        $reflectionInstaller = new ReflectionClass($className);
        if (!$reflectionInstaller->isSubclassOf('Zikula_AbstractInstaller')) {
            throw new \RuntimeException($this->__f("%s must be an instance of Zikula_AbstractInstaller", $className));
        }
        $installer = $reflectionInstaller->newInstanceArgs(array($this->serviceManager, $module));

        // perform the actual install of the module
        // system or module
        $func = array($installer, 'install');
        if (is_callable($func)) {
            if (call_user_func($func) != true) {
                return false;
            }
        }

        // Update state of module
        if (!$this->setState(array('id' => $args['id'], 'state' => ModUtil::STATE_ACTIVE))) {
            throw new \RuntimeException($this->__('Error! Could not change module state.'));
        }

        if (!System::isInstalling()) {
            // This should become an event handler - drak
            $category = ModUtil::getVar('ZikulaAdminModule', 'defaultcategory');
            ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'addmodtocategory', array('module' => $modinfo['name'], 'category' => $category));
        }

        // All went ok so issue installed event
        $event = new \Zikula\Core\Event\GenericEvent(null, $modinfo);
        $this->getDispatcher()->dispatch('installer.module.installed', $event);

        // Success
        return true;
    }

    /**
     * Upgrade a module.
     *
     * @param mixed[] $args {<ul>
     *      <li>@type int     $id                  The module ID</li>
     *      <li>@type boolean $interactive_upgrade Whether or not to upgrade in interactive mode</li>
     *                       </ul>}
     *
     * @return boolean True on success, false on failure
     *
     * @throws \InvalidArgumentException Thrown if the module id parameter is either not set or not numeric
     * @throws NotFoundHttpException Thrown if the module id isn't a valid module
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

        $entity = 'Zikula\Core\Doctrine\Entity\ExtensionEntity';

        // Get module information
        $modinfo = ModUtil::getInfo($args['id']);
        if (empty($modinfo)) {
            throw new NotFoundHttpException($this->__('Error! No such module ID exists.'));
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

        // load module maintainence functions
        if (false === strpos($osdir, '/')) {
            ZLoader::addAutoloader($osdir, array($modpath, "$modpath/$osdir/lib"));
        }

        $bootstrap = "$modpath/$osdir/bootstrap.php";
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }

        if ($modinfo['type'] == ModUtil::TYPE_MODULE) {
            if (is_dir("modules/$osdir/Resources/locale") || is_dir("modules/$osdir/locale")) {
                ZLanguage::bindModuleDomain($modinfo['name']);
            }
        }

        $module = ModUtil::getModule($modinfo['name']);
        if (null === $module) {
            $className = ucwords($modinfo['name']).'\\'.ucwords($modinfo['name']).'Installer';
            $classNameOld = ucwords($modinfo['name']) . '_Installer';
            $className = class_exists($className) ? $className : $classNameOld;
        } else {
            $className = $module->getInstallerClass();
        }
        $reflectionInstaller = new ReflectionClass($className);
        if (!$reflectionInstaller->isSubclassOf('Zikula_AbstractInstaller')) {
            throw new \RuntimeException($this->__f("%s must be an instance of Zikula_AbstractInstaller", $className));
        }
        $installer = $reflectionInstaller->newInstanceArgs(array($this->serviceManager, $module ));

        // perform the actual upgrade of the module
        $func = array($installer, 'upgrade');

        if (is_callable($func)) {
            $result = call_user_func($func, $modinfo['version']);
            if (is_string($result)) {
                if ($result != $modinfo['version']) {
                    // update the last successful updated version
                    $item = $this->entityManager->getRepository($entity)->find($modinfo['id']);
                    $item['version'] = $result;
                    $this->entityManager->flush();
                }

                return false;
            } elseif ($result != true) {
                return false;
            }
        }
        $modversion['version'] = '0';

        $modversion = ExtensionsUtil::getVersionMeta($modinfo['name'], $modpath);
        $version = $modversion['version'];

        // Update state of module
        $result = $this->setState(array('id' => $args['id'], 'state' => ModUtil::STATE_ACTIVE));
        if ($result) {
            LogUtil::registerStatus($this->__("Done! Module has been upgraded. Its status is now 'Active'."));
        } else {
            return false;
        }

        // update the module with the new version
        $item = $this->entityManager->getRepository($entity)->find($args['id']);
        $item['version'] = $version;
        $this->entityManager->flush();

        // Upgrade succeeded, issue event.
        $event = new \Zikula\Core\Event\GenericEvent(null, $modinfo);
        $this->getDispatcher()->dispatch('installer.module.upgraded', $event);

        // Success
        return true;
    }

    /**
     * Upgrade all modules.
     *
     * @return array An array of upgrade results, indexed by module name.
     */
    public function upgradeall()
    {
        $upgradeResults = array();

        // regenerate modules list
        $filemodules = $this->getfilemodules();
        $this->regenerate(array('filemodules' => $filemodules));

        // get a list of modules needing upgrading
        if ($this->listmodules(array('state' => ModUtil::STATE_UPGRADED))) {
            $newmods = $this->listmodules(array('state' => ModUtil::STATE_UPGRADED));

            // Sort upgrade order according to this list.
            $priorities = array('ZikulaExtensionsModule', 'ZikulaUsersModule' , 'ZikulaGroupsModule', 'ZikulaPermissionsModule', 'ZikulaAdminModule', 'ZikulaBlocksModule', 'ZikulaThemeModule', 'ZikulaSettingsModule', 'ZikulaCategoriesModule', 'ZikulaSecurityCenterModule', 'ZikulaErrorsModule');
            $sortedList = array();
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
                $upgradeResults[$mod['name']] = $this->upgrade(array('id' => $mod['id']));
            }

            System::setVar('Version_Num', Zikula_Core::VERSION_NUM);
        }

        return $upgradeResults;
    }

    /**
     * Utility function to count the number of items held by this module.
     *
     * @param mixed[] $args {<ul>
     *      <li>@type string $letter Filter the count by the first letter of the module name; optional</li>
     *      <li>@type int    $state  Filter the count by the module state; optional</li>
     *                       </ul>}
     *
     * @return integer The number of items held by this module.
     */
    public function countitems($args)
    {
        // create a QueryBuilder instance
        $qb = $this->entityManager->createQueryBuilder();

        // add select and from params
        $qb->select('COUNT(e.id)')
           ->from('Zikula\Core\Doctrine\Entity\ExtensionEntity', 'e');

        // filter by first letter of module
        if (isset($args['letter']) && !empty($args['letter'])) {
            $clause1 = $qb->expr()->like('e.name', $qb->expr()->literal($args['letter'] . '%'));
            $clause2 = $qb->expr()->like('e.name', $qb->expr()->literal(strtolower($args['letter']) . '%'));
            $qb->andWhere($clause1 . ' OR ' . $clause2);
        }

        // filter by type
        $type = (empty($args['type']) || $args['type'] < 0 || $args['type'] > ModUtil::TYPE_SYSTEM) ? 0 : (int)$args['type'];
        if ($type != 0) {
            $qb->andWhere($qb->expr()->eq('e.type', $qb->expr()->literal($type)));
        }

        if ($this->serviceManager['multisites.enabled'] == 1) {
            $state = (empty($args['state']) || $args['state'] < -1 || $args['state'] > ModUtil::STATE_NOTALLOWED) ? 0 : (int)$args['state'];
        } else {
            $state = (empty($args['state']) || $args['state'] < -1 || $args['state'] > ModUtil::STATE_UPGRADED) ? 0 : (int)$args['state'];
        }

        // filter by module state
        if ($this->serviceManager['multisites.enabled'] == 1) {
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
                $qb->andWhere($qb->expr()->eq('e.state', $qb->expr()->literal($state)));
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
     * Get available admin panel links.
     *
     * @return array An array of admin links.
     */
    public function getlinks()
    {
        $links = array();

        // assign variables from input
        $startnum = (int)FormUtil::getPassedValue('startnum', null, 'GET');
        $letter = FormUtil::getPassedValue('letter', null, 'GET');

        if (SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('ZikulaExtensionsModule', 'admin', 'view'),
                             'text' => $this->__('Modules list'),
                             'icon' => 'list',
                             'links' => array(
                                             array('url' => ModUtil::url('ZikulaExtensionsModule', 'admin', 'view'),
                                                   'text' => $this->__('All')),
                                             array('url' => ModUtil::url('ZikulaExtensionsModule', 'admin', 'view', array('state'=>ModUtil::STATE_UNINITIALISED)),
                                                   'text' => $this->__('Not installed')),
                                             array('url' => ModUtil::url('ZikulaExtensionsModule', 'admin', 'view', array('state'=>ModUtil::STATE_INACTIVE)),
                                                   'text' => $this->__('Inactive')),
                                             array('url' => ModUtil::url('ZikulaExtensionsModule', 'admin', 'view', array('state'=>ModUtil::STATE_ACTIVE)),
                                                   'text' => $this->__('Active')),
                                             array('url' => ModUtil::url('ZikulaExtensionsModule', 'admin', 'view', array('state'=>ModUtil::STATE_MISSING)),
                                                   'text' => $this->__('Files missing')),
                                             array('url' => ModUtil::url('ZikulaExtensionsModule', 'admin', 'view', array('state'=>ModUtil::STATE_UPGRADED)),
                                                   'text' => $this->__('New version uploaded')),
                                             array('url' => ModUtil::url('ZikulaExtensionsModule', 'admin', 'view', array('state'=>ModUtil::STATE_INVALID)),
                                                   'text' => $this->__('Invalid structure'))
                                               ));

            $links[] = array('url' => ModUtil::url('ZikulaExtensionsModule', 'admin', 'viewPlugins'),
                             'text' => $this->__('Plugins list'),
                             'icon' => 'table',
                             'links' => array(
                                             array('url' => ModUtil::url('ZikulaExtensionsModule', 'admin', 'viewPlugins'),
                                                   'text' => $this->__('All')),
                                             array('url' => ModUtil::url('ZikulaExtensionsModule', 'admin', 'viewPlugins', array('state'=>PluginUtil::NOTINSTALLED)),
                                                   'text' => $this->__('Not installed')),
                                             array('url' => ModUtil::url('ZikulaExtensionsModule', 'admin', 'viewPlugins', array('state'=>PluginUtil::DISABLED)),
                                                   'text' => $this->__('Inactive')),
                                             array('url' => ModUtil::url('ZikulaExtensionsModule', 'admin', 'viewPlugins', array('state'=>PluginUtil::ENABLED)),
                                                   'text' => $this->__('Active'))
                                               ));

            $links[] = array('url' => ModUtil::url('ZikulaExtensionsModule', 'admin', 'viewPlugins', array('systemplugins' => true)),
                             'text' => $this->__('System Plugins'),
                             'icon' => 'table',
                             'links' => array(
                                             array('url' => ModUtil::url('ZikulaExtensionsModule', 'admin', 'viewPlugins', array('systemplugins' => true)),
                                                   'text' => $this->__('All')),
                                             array('url' => ModUtil::url('ZikulaExtensionsModule', 'admin', 'viewPlugins', array('systemplugins' => true, 'state'=>PluginUtil::NOTINSTALLED)),
                                                   'text' => $this->__('Not installed')),
                                             array('url' => ModUtil::url('ZikulaExtensionsModule', 'admin', 'viewPlugins', array('systemplugins' => true, 'state'=>PluginUtil::DISABLED)),
                                                   'text' => $this->__('Inactive')),
                                             array('url' => ModUtil::url('ZikulaExtensionsModule', 'admin', 'viewPlugins', array('systemplugins' => true, 'state'=>PluginUtil::ENABLED)),
                                                   'text' => $this->__('Active'))
                                               ));


            $links[] = array('url' => ModUtil::url('ZikulaExtensionsModule', 'admin', 'modifyconfig'), 'text' => $this->__('Settings'), 'icon' => 'wrench');
            //$filemodules = ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'getfilemodules');
            //ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'regenerate', array('filemodules' => $filemodules));

            // get a list of modules needing upgrading
            $newmods = ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'listmodules', array('state' => ModUtil::STATE_UPGRADED));
            if ($newmods) {
                $links[] = array('url' => ModUtil::url('ZikulaExtensionsModule', 'admin', 'upgradeall'), 'text' => $this->__('Upgrade All'), 'icon' => 'wrench');
            }
        }

        return $links;
    }

    /**
     * Get all module dependencies.
     *
     * @deprecated since 1.3.6 
     * @todo remove in 1.4.0
     *
     * @use $this->getalldependencies instead.
     *
     * @return array Array of dependencies.
     */
    public function getdallependencies()
    {
        return $this->getalldependencies();
    }

    /**
     * Get all module dependencies.
     *
     * @return array Array of dependencies.
     */
    public function getalldependencies()
    {
        $dependencies = $this->entityManager->getRepository('Zikula\Core\Doctrine\Entity\ExtensionDependencyEntity')->findBy(array(), array('modid' => 'ASC'));

        return $dependencies;
    }

    /**
     * Get dependencies for a module.
     *
     * @param int[] $args {<ul>
     *      <li>@type int $modid Id of module to get dependencies for</li>
     *                     </ul>}
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

        $dependencies = $this->entityManager->getRepository('Zikula\Core\Doctrine\Entity\ExtensionDependencyEntity')->findBy(array('modid' => $args['modid']));

        return $dependencies;
    }

    /**
     * Get dependents of a module.
     *
     * @param int[] $args {<ul>
     *      <li>@type int $modid Id of module to get dependants for</li>
     *                     </ul>}
     *
     * @return array|boolean Array of dependents; false otherwise.
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

        $dependents = $this->entityManager->getRepository('Zikula\Core\Doctrine\Entity\ExtensionDependencyEntity')->findBy(array('modname' => $modinfo['name']));

        return $dependents;
    }

    /**
     * Check modules for consistency.
     *
     * @param array[] $args {<ul>
     *      <li>@type array $filemodules Array of modules in the filesystem, as returned by {@link getfilemodules()}</li>
     *                       </ul>}
     *
     * @see    getfilemodules()
     *
     * @return array An array of arrays with links to inconsistencies
     *
     * @throws \InvalidArgumentException Thrown if the filemodules parameter is either not set or not an array
     * @throws AccessDeniedHttpException Thrown if the user doesn't have admin permissions over the module
     */
    public function checkconsistency($args)
    {
        // Security check
        if (!System::isInstalling()) {
            if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
                throw new AccessDeniedHttpException();
            }
        }

        // Argument check
        if (!isset($args['filemodules']) || !is_array($args['filemodules'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $filemodules = $args['filemodules'];

        $modulenames = array();
        $displaynames = array();

        $errors_modulenames = array();
        $errors_displaynames = array();

        // check for duplicate names or display names
        foreach ($filemodules as $dir => $modinfo) {
            if (isset($modulenames[strtolower($modinfo['name'])])) {
                $errors_modulenames[] = array('name' => $modinfo['name'],
                        'dir1' => $modulenames[strtolower($modinfo['name'])],
                        'dir2' => $dir);
            }

            if (isset($displaynames[strtolower($modinfo['displayname'])])) {
                $errors_displaynames[] = array('name' => $modinfo['displayname'],
                        'dir1' => $displaynames[strtolower($modinfo['displayname'])],
                        'dir2' => $dir);
            }

            if (isset($displaynames[strtolower($modinfo['url'])])) {
                $errors_displaynames[] = array('name' => $modinfo['url'],
                        'dir1' => $displaynames[strtolower($modinfo['url'])],
                        'dir2' => $dir);
            }

            $modulenames[strtolower($modinfo['name'])] = $dir;
            $displaynames[strtolower($modinfo['displayname'])] = $dir;
        }

        // do we need to check for duplicate oldnames as well?
        return array('errors_modulenames'  => $errors_modulenames,
                     'errors_displaynames' => $errors_displaynames);
    }

    /**
     * Check if a module comes from the core.
     *
     * @param string[] $args {<ul>
     *      <li>@type string $modulename The name of the module to check.</li>
     *                        </ul>}
     *
     * @return boolean True if it's a core module; otherwise false.
     */
    public function iscoremodule($args)
    {
        // todo: get rid of this when we remove Forms
        if ($args['modulename'] === 'ZikulaPageLockModule') {
            return false;
        }

        return ModUtil::getModuleBaseDir($args['modulename']) === 'system' ? true : false;
    }
}
