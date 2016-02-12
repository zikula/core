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

namespace Zikula\ExtensionsModule\Controller;

use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Event\ModuleStateEvent;
use Zikula\Core\CoreEvents;
use Zikula_View;
use ModUtil;
use SecurityUtil;
use ZLanguage;
use Zikula\ExtensionsModule\Util as ExtensionsUtil;
use SessionUtil;
use PluginUtil;
use Zikula_View_Theme;
use Zikula_Plugin_AlwaysOnInterface;
use Zikula_Plugin_ConfigurableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Symfony\Component\Routing\RouterInterface;
use HookUtil;
use vierbergenlars\SemVer\expression;
use vierbergenlars\SemVer\version;
use Zikula\Bundle\CoreBundle\Bundle\MetaData;

/**
 * No need for a route prefix, as there isn't a user controller.
 *
 * Administrative controllers for the extensions module
 */
class AdminController extends \Zikula_AbstractController
{
    const NEW_ROUTES_AVAIL = 'new.routes.avail';

    /**
     * @var array packages as read from the composer.lock file
     */
    private $installedPackages = array();

    /**
     * Post initialise.
     *
     * @return void
     */
    protected function postInitialize()
    {
        // In this controller we do not want caching.
        $this->view->setCaching(Zikula_View::CACHE_DISABLED);
    }

    /**
     * @Route("")
     *
     * Extensions Module main admin function
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        // Security check will be done in view()
        return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_view', array(), RouterInterface::ABSOLUTE_URL));
    }

    /**
     * Route not needed here because method is legacy-only
     *
     * Extensions Module main admin function
     *
     * @deprecated since 1.4.0 use indexAction instead
     *
     * @return RedirectResponse
     */
    public function mainAction()
    {
        // Security check will be done in view()
        return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_view', array(), RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/modules/modify/{id}/{restore}", requirements={"id" = "^[1-9]\d*$", "restore" = "0|1"})
     * @Method("GET")
     *
     * Modify a module.
     *
     * @param integer $id
     * @param boolean $restore
     *
     * @return Response symfony response object
     *
     * @throws \InvalidArgumentException Thrown if the id parameter is not provided or not numeric
     * @throws NotFoundHttpException Thrown if the requested module id doesn't exist
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission to the requested module
     */
    public function modifyAction($id, $restore = false)
    {
        $obj = ModUtil::getInfo($id);
        if ($obj == false) {
            throw new NotFoundHttpException($this->__('Error! No such module ID exists.'));
        }

        if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', $obj['name'].'::'.$id, ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        if ($restore) {
            // load the version array
            $baseDir = ($obj['type'] == ModUtil::TYPE_SYSTEM) ? 'system' : 'modules';

            // load gettext domain for 3rd party modules
            if ($baseDir == 'modules' &&
                (is_dir('modules/'.$obj['directory'].'/Resources/locale') || is_dir('modules/'.$obj['directory'].'/locale'))
            ) {
                // This is required here since including pnversion automatically executes the pnversion code
                // this results in $this->__() caching the result before the domain is bounded.  Will not occur in zOO
                // since loading is self contained in each zOO application.
                ZLanguage::bindModuleDomain($obj['name']);
            }

            $modversion = ExtensionsUtil::getVersionMeta($obj['name'], $baseDir);

            // load defaults
            $name = (isset($modversion['name']) ? $modversion['name'] : '');
            $displayname = (isset($modversion['displayname']) ? $modversion['displayname'] : $name);
            $url = (isset($modversion['url']) ? $modversion['url'] : $displayname);
            $description = (isset($modversion['description']) ? $modversion['description'] : '');

            $obj = array(
                    'id' => $id,
                    'displayname' => $displayname,
                    'url' => $url,
                    'description' => $description);
        }

        $this->view->assign($obj);

        // Return the output that has been generated by this function
        return new Response($this->view->fetch('Admin/modify.tpl'));
    }

    /**
     * @Route("/modules/modify")
     * @Method("POST")
     *
     * Update a module
     *
     * @param Request $request
     *
     *  int    'id'             module id
     *  string 'newdisplayname' new display name of the module
     *  string 'newdescription' new description of the module
     *  string 'newurl'         new url of the module
     *
     * @return RedirectResponse
     */
    public function updateAction(Request $request)
    {
        $this->checkCsrfToken();

        // Get parameters
        $id = (int) $request->request->get('id', null);
        $newdisplayname = $request->request->get('newdisplayname', null);
        $newdescription = $request->request->get('newdescription', null);
        $newurl = $request->request->get('newurl', null);

        // Pass to API
        if (ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'update', array(
                'id' => $id,
                'displayname' => $newdisplayname,
                'description' => $newdescription,
                'url' => $newurl))) {
            // Success
            $request->getSession()->getFlashBag()->add('status', $this->__('Done! Saved module information.'));
        } else {
            return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_view', array('id' => $id), RouterInterface::ABSOLUTE_URL));
        }

        return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_view', array(), RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/modules")
     *
     * List modules and current settings
     *
     * @param Request $request
     *
     * @return Response symfony response object
     *
     * @throws \RuntimeException Thrown if the module list cannot be regenerated
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission to the module
     */
    public function viewAction(Request $request)
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // flag indicating whether we need to dump the js routes
        $redirectForJsRouteDumpRequired = false;

        // check for just installed module and fire event
        $modulesPostInstall = $request->query->get('postinstall', null);
        if (!empty($modulesPostInstall)) {
            $modulesPostInstall = json_decode($modulesPostInstall);
            foreach ($modulesPostInstall as $justInstalled) {
                $modInfo = ModUtil::getInfo($justInstalled);
                $module = ModUtil::getModule($modInfo['name']);
                if (!empty($module)) {
                    $event = new ModuleStateEvent($module);
                    $this->getDispatcher()->dispatch(CoreEvents::MODULE_POSTINSTALL, $event);
                }
            }
            // because the Symfony cache is renewed we need to dump the js routes in the next request
            $redirectForJsRouteDumpRequired = true;
        }

        // Get parameters from whatever input we need.
        $modinfo = $this->getModInfo();
        $startnum = (int) $request->query->get('startnum', 1) - 1;
        $letter = $request->query->get('letter', null);
        // $state can come from GET or POST
        $state = $request->get('state', (!strstr($request->server->get('HTTP_REFERER'), 'module='.$modinfo['url'])) ? null : SessionUtil::getVar('state', null));
        $sort = $request->query->get('sort', (!strstr($request->server->get('HTTP_REFERER'), 'module='.$modinfo['url'])) ? null : SessionUtil::getVar('sort', null));
        $sortdir = $request->query->get('sortdir', (!strstr($request->server->get('HTTP_REFERER'), 'module='.$modinfo['url'])) ? null : SessionUtil::getVar('sortdir', null));

        if ($redirectForJsRouteDumpRequired === true) {
            return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_view',
                                                 array('startnum' => $startnum,
                                                       'letter' => $letter,
                                                       'state' => $state,
                                                       'justinstalled' => json_encode($modulesPostInstall)), RouterInterface::ABSOLUTE_URL));
        } else {
            $modulesJustInstalled = $request->query->get('justinstalled', null);
            if (!empty($modulesJustInstalled)) {
                // alert the system that new routes are available (ids of modules just installed avail as args)
                $event = new GenericEvent(null, json_decode($modulesJustInstalled));
                $this->getDispatcher()->dispatch(self::NEW_ROUTES_AVAIL, $event);
            }
        }

        // parameter for used sort order
        if ($sort != 'name' && $sort != 'displayname') {
            $sort = 'name';
        }
        if ($sortdir != 'ASC' && $sortdir != 'DESC') {
            $sortdir = 'ASC';
        }

        // save the current values
        SessionUtil::setVar('state', $state);
        SessionUtil::setVar('sort', $sort);
        SessionUtil::setVar('sortdir', $sortdir);

        if ($this->serviceManager['multisites.enabled'] != 1
            || ($this->serviceManager['multisites.mainsiteurl'] == $request->query->get('sitedns', null)
                && $this->serviceManager['multisites.based_on_domains'] == 0)
            || ($this->serviceManager['multisites.mainsiteurl'] == $_SERVER['HTTP_HOST']
                && $this->serviceManager['multisites.based_on_domains'] == 1)) {
            // always regenerate modules list
            $filemodules = ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'getfilemodules');
            $inconsistencies = ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'checkconsistency', array('filemodules' => $filemodules));

            if (!(empty($inconsistencies['errors_modulenames']) && empty($inconsistencies['errors_displaynames']))) {
                $this->view->assign('errors_modulenames', $inconsistencies['errors_modulenames'])
                           ->assign('errors_displaynames', $inconsistencies['errors_displaynames']);

                return new Response($this->view->fetch('Admin/regenerate_errors.tpl'));
            }

            // No inconsistencies, so we can regenerate modules
            $defaults = (int) $request->query->get('defaults', false);
            if (!ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'regenerate', array('filemodules' => $filemodules, 'defaults' => $defaults))) {
                throw new \RuntimeException($this->__('Errors were detected regenerating the modules list from file system.'));
            }
        }

        // assign the state filter
        $this->view->assign('state', $state);

        // Get list of modules
        $mods = ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'listmodules',
                                 array('startnum' => $startnum,
                                       'letter' => $letter,
                                       'state' => $state,
                                       'numitems' => $this->getVar('itemsperpage'),
                                       'sortdir' => $sortdir,
                                       'sort' => $sort));

        // generate an auth key to use in urls
        $csrftoken = SecurityUtil::generateCsrfToken($this->getContainer(), true);

        $moduleinfo = array();
        if (!empty($mods)) {
            foreach ($mods as $mod) {
                $mod = $mod->toArray();

                // Add applicable actions
                $actions = array();

                if (SecurityUtil::checkPermission('ZikulaExtensionsModule::', "$mod[name]::$mod[id]", ACCESS_ADMIN)) {
                    switch ($mod['state']) {
                        case ModUtil::STATE_ACTIVE:
                            if (!ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'iscoremodule', array('modulename' => $mod['name']))) {
                                $actions[] = array(
                                        'url' => $this->get('router')->generate('zikulaextensionsmodule_admin_deactivate', array(
                                        'id' => $mod['id'],
                                        'startnum' => $startnum,
                                        'csrftoken' => $csrftoken,
                                        'letter' => $letter,
                                        'state' => $state)),
                                        'image' => 'minus-circle text-danger',
                                        'color' => '#c00',
                                        'title' => $this->__f('Deactivate \'%s\' module', $mod['name']));
                            }

                            if (PluginUtil::hasModulePlugins($mod['name'])) {
                                $actions[] = array(
                                        'url' => $this->get('router')->generate('zikulaextensionsmodule_admin_viewplugins', array(
                                        'bymodule' => $mod['name'])),
                                        'image' => 'paperclip',
                                        'color' => 'black',
                                        'title' => $this->__f('Plugins for \'%s\'', $mod['name']));
                            }
                            break;

                        case ModUtil::STATE_INACTIVE:
                            $actions[] = array(
                                    'url' => $this->get('router')->generate('zikulaextensionsmodule_admin_activate', array(
                                    'id' => $mod['id'],
                                    'startnum' => $startnum,
                                    'csrftoken' => $csrftoken,
                                    'letter' => $letter,
                                    'state' => $state)),
                                    'image' => 'plus-square text-success',
                                    'color' => '#0c0',
                                    'title' => $this->__f('Activate \'%s\'', $mod['name']));
                            $actions[] = array(
                                    'url' => $this->get('router')->generate('zikulaextensionsmodule_admin_remove', array(
                                    'id' => $mod['id'],
                                    'startnum' => $startnum,
                                    'letter' => $letter,
                                    'state' => $state)),
                                    'image' => 'trash-o',
                                    'color' => '#c00',
                                    'title' => $this->__f('Uninstall \'%s\' module', $mod['name']));
                            break;

                        case ModUtil::STATE_MISSING:
                            // Nothing to do.
                            break;
                        case ModUtil::STATE_UPGRADED:
                            $actions[] = array(
                                    'url' => $this->get('router')->generate('zikulaextensionsmodule_admin_upgrade', array(
                                    'id' => $mod['id'],
                                    'startnum' => $startnum,
                                    'csrftoken' => $csrftoken,
                                    'secret' => $this->getContainer()->getParameter('url_secret'),
                                    'letter' => $letter,
                                    'state' => $state)),
                                    'image' => 'refresh',
                                    'color' => '#00c',
                                    'title' => $this->__f('Upgrade \'%s\'', $mod['name']));
                            break;

                        case ModUtil::STATE_INVALID:
                            // nothing to do.
                            // do not allow deletion of invalid modules if previously installed (#1278)
                            break;

                        case ModUtil::STATE_NOTALLOWED:
                            $actions[] = array(
                                    'url' => $this->get('router')->generate('zikulaextensionsmodule_admin_remove', array(
                                    'id' => $mod['id'],
                                    'startnum' => $startnum,
                                    'csrftoken' => $csrftoken,
                                    'letter' => $letter,
                                    'state' => $state)),
                                    'image' => 'trash-o',
                                    'color' => '#c00',
                                    'title' => $this->__f('Remove \'%s\' module', $mod['name']));
                            break;

                        case ModUtil::STATE_UNINITIALISED:
                        default:
                            if ($mod['state'] < 10) {
                                $actions[] = array(
                                        'url' => $this->get('router')->generate('zikulaextensionsmodule_admin_initialise', array(
                                        'id' => $mod['id'],
                                        'startnum' => $startnum,
                                        'csrftoken' => $csrftoken,
                                        'letter' => $letter,
                                        'state' => $state)),
                                        'image' => 'cog text-success',
                                        'color' => '#0c0',
                                        'title' => $this->__f('Install \'%s\'', $mod['name']));
                            } else {
                                $actions[] = array(
                                        'url' => $this->get('router')->generate('zikulaextensionsmodule_admin_compinfo', array(
                                        'id' => $mod['id'],
                                        'startnum' => $startnum,
                                        'letter' => $letter,
                                        'state' => $state)),
                                        'image' => 'info-circle',
                                        'color' => 'black',
                                        'title' => $this->__f('Incompatible version: \'%s\'', $mod['name']));
                            }
                            break;
                    }

                    // RNG: can't edit an invalid module
                    if ($mod['state'] != ModUtil::STATE_INVALID) {
                        $actions[] = array(
                                'url' => $this->get('router')->generate('zikulaextensionsmodule_admin_modify', array(
                                'id' => $mod['id'])),
                                'image' => 'wrench',
                                'color' => 'black',
                                'title' => $this->__f('Edit \'%s\'', $mod['name']));
                    }
                }

                // Translate state
                switch ($mod['state']) {
                    case ModUtil::STATE_INACTIVE:
                        $status = $this->__('Inactive');
                        $statusclass = "warning";
                        break;

                    case ModUtil::STATE_ACTIVE:
                        $status = $this->__('Active');
                         $statusclass = "success";
                        break;

                    case ModUtil::STATE_MISSING:
                        $status = $this->__('Files missing');
                        $statusclass = "danger";
                        break;

                    case ModUtil::STATE_UPGRADED:
                        $status = $this->__('New version');
                        $statusclass = "danger";
                        break;

                    case ModUtil::STATE_INVALID:
                        $status = $this->__('Invalid structure');
                        $statusclass = "danger";
                        break;

                    case ModUtil::STATE_NOTALLOWED:
                        $status = $this->__('Not allowed');
                        $statusclass = "danger";
                        break;

                    case ModUtil::STATE_UNINITIALISED:
                    default:
                        if ($mod['state'] > 10) {
                            $status = $this->__('Incompatible');
                            $statusclass = "info";
                        } else {
                            $status = $this->__('Not installed');
                            $statusclass = "primary";
                        }
                        break;
                }

                // get new version number for ModUtil::STATE_UPGRADED
                if ($mod['state'] == ModUtil::STATE_UPGRADED) {
                    $mod['newversion'] = $filemodules[$mod['name']]['version'];
                }

                $moduleinfo[] = array(
                        'modinfo' => $mod,
                        'status' => $status,
                        'statusclass' => $statusclass,
                        'options' => $actions);
            }
        }

        $this->view->assign('multi', $this->serviceManager['multisites.enabled'])
                   ->assign('sort', $sort)
                   ->assign('sortdir', $sortdir)
                   ->assign('modules', $moduleinfo);

        // Assign the values for the smarty plugin to produce a pager.
        $this->view->assign('pager', array('numitems' => ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'countitems', array('letter' => $letter, 'state' => $state)),
                                           'itemsperpage' => $this->getVar('itemsperpage')));

        // Return the output that has been generated by this function
        return new Response($this->view->fetch('Admin/view.tpl'));
    }

    /**
     * @Route("/modules/initialize")
     *
     * Initialise a module.
     *
     * @param Request $request
     *
     * @return bool true
     *
     * @throws \InvalidArgumentException Thrown if the module id isn't set or isn't numeric
     */
    public function initialiseAction(Request $request)
    {
        $csrftoken = $request->get('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // Get parameters from whatever input we need
        $id = (int) $request->get('id', 0);
        $confirmation = (bool) $request->get('confirmation', false);
        $startnum = (int) $request->get('startnum');
        $letter = $request->get('letter');
        $state = (int)$request->get('state');

        // assign any dependencies - filtering out non-active module dependents
        $fataldependency = false;
        if ($id != 0) {
            $dependencies = ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'getdependencies', array('modid' => $id));

            $modulenotfound = false;
            if (!$confirmation && $dependencies) {
                foreach ($dependencies as $key => $dependency) {
                    $dependencyArray = $dependency->toArray();
                    if ($this->bundleDependencySatisfied($dependencyArray)) {
                        unset($dependencies[$key]);
                        continue;
                    }
                    $dependencies[$key] = $dependencyArray;
                    $dependencies[$key]['insystem'] = true;
                    $modinfo = ModUtil::getInfoFromName($dependency['modname']);
                    $base = ($modinfo['type'] == ModUtil::TYPE_MODULE) ? 'modules' : 'system';
                    if ((is_dir($base.'/'.$dependency['modname'])) || (is_dir(ModUtil::getModuleRelativePath($dependency['modname'])))) {
                        $minok = 0;
                        $maxok = 0;
                        $modversion = ExtensionsUtil::getVersionMeta($dependency['modname'], $base);

                        if (!empty($dependency['minversion'])) {
                            $minok = version_compare($modversion['version'], $dependency['minversion']);
                        }

                        if (!empty($dependency['maxversion'])) {
                            $maxok = version_compare($dependency['maxversion'], $modversion['version']);
                        }

                        if ($minok == -1 || $maxok == -1) {
                            if ($dependency['status'] == ModUtil::DEPENDENCY_REQUIRED) {
                                $fataldependency = true;
                            } else {
                                unset($dependencies[$key]);
                            }
                        } else {
                            $dependencies[$key] = array_merge($dependencies[$key], $modinfo);
                            // if this module is already installed, don't display it in the list of dependencies.
                            if (isset($dependencies[$key]['state']) && ($dependencies[$key]['state'] > ModUtil::STATE_UNINITIALISED && $dependencies[$key]['state'] < ModUtil::STATE_NOTALLOWED)) {
                                unset($dependencies[$key]);
                            }
                        }
                    } elseif (!empty($modinfo)) {
                        $dependencies[$key] = array_merge($dependencies[$key], $modinfo);
                    } else {
                        $dependencies[$key]['insystem'] = false;
                        $modulenotfound = true;
                        if ($dependency['status'] == ModUtil::DEPENDENCY_REQUIRED) {
                            $fataldependency = true;
                        }
                    }
                }

                $this->view->assign('fataldependency', $fataldependency);

                // we have some dependencies so let's warn the user about these
                if (!empty($dependencies)) {
                    return new Response($this->view->assign('id', $id)
                                      ->assign('dependencies', $dependencies)
                                      ->assign('modulenotfound', $modulenotfound)
                                      ->fetch('Admin/initialise.tpl'));
                }
            } else {
                $dependencies = (array)$request->request->get('dependencies', array());
            }
        }

        if (empty($id) || !is_numeric($id)) {
            throw new \InvalidArgumentException($this->__('Error! No module ID provided.'));
        }

        $modulesInstalled = array();

        // initialise and activate any dependencies
        if (isset($dependencies) && is_array($dependencies)) {
            foreach ($dependencies as $dependency) {
                if (!ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'initialise',
                                      array('id' => $dependency))) {
                    return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_view', array(
                            'startnum' => $startnum,
                            'letter' => $letter,
                            'state' => $state), RouterInterface::ABSOLUTE_URL));
                }
                if (!ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'setstate',
                                      array('id' => $dependency,
                                            'state' => ModUtil::STATE_ACTIVE))) {
                    return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_view', array(
                            'startnum' => $startnum,
                            'letter' => $letter,
                            'state' => $state), RouterInterface::ABSOLUTE_URL));
                }
                $modulesInstalled[] = $dependency;
            }
        }

        // Now we've initialised the dependencies initialise the main module
        $res = (bool)ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'initialise', array('id' => $id));
        $modinfo = ModUtil::getInfo($id);

        if ($res) {
            // Success
            $request->getSession()->getFlashBag()->add('status', $this->__f('Done! Installed %s.', $modinfo['name']));

            if (ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'setstate',
                                 array('id' => $id,
                                       'state' => ModUtil::STATE_ACTIVE))) {
                // Success
                $request->getSession()->getFlashBag()->add('status', $this->__f('Done! Activated %s.', $modinfo['name']));
            }
            $modulesInstalled[] = $id;

            return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_view',
                                                 array('startnum' => $startnum,
                                                       'letter' => $letter,
                                                       'state' => $state,
                                                       'postinstall' => json_encode($modulesInstalled)), RouterInterface::ABSOLUTE_URL));
        } else {
            $request->getSession()->getFlashBag()->add('error', $this->__f('Initialization of %s failed!', $modinfo['name']));

            return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_view',
                                                 array('startnum' => $startnum,
                                                       'letter' => $letter,
                                                       'state' => $state), RouterInterface::ABSOLUTE_URL));
        }
    }

    /**
     * @Route("/modules/activate/{id}", requirements={"id" = "^[1-9]\d*$"})
     * @Method("GET")
     *
     * Activate a module
     *
     * @param Request $request
     * @param integer $id
     *
     *  int 'startnum' starting number from the pager
     *  string 'letter' letter from the filter
     *  string 'state' state from the filter
     *
     * @return RedirectResponse
     */
    public function activateAction(Request $request, $id)
    {
        $csrftoken = $request->query->get('csrftoken');
        $this->checkCsrfToken($csrftoken);

        $startnum = (int) $request->query->get('startnum', null);
        $letter = $request->query->get('letter', null);
        $state = $request->query->get('state', null);

        $moduleinfo = ModUtil::getInfo($id);
        if ($moduleinfo['state'] == ModUtil::STATE_NOTALLOWED) {
            $request->getSession()->getFlashBag()->add('error', $this->__f('Error! Activation of module %s not allowed.', $moduleinfo['name']));
        } else {
            // Update state
            $setstate = ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'setstate', array('id' => $id, 'state' => ModUtil::STATE_ACTIVE));
            if ($setstate) {
                // Success
                $event = new GenericEvent(null, $moduleinfo);
                $this->getDispatcher()->dispatch('installer.module.activated', $event);
                $this->get('zikula.cache_clearer')->clear('symfony.routing');
                $request->getSession()->getFlashBag()->add('status', $this->__f('Done! Activated %s module.', $moduleinfo['name']));
            }
        }

        return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_view',
                                             array('startnum' => $startnum,
                                                   'letter' => $letter,
                                                   'state' => $state), RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/modules/upgrade/{id}", requirements={"id" = "^[1-9]\d*$"})
     *
     * Upgrade a module
     *
     * @param Request $request
     * @param integer $id
     *
     *  int 'startnum' starting number from the pager
     *  string 'letter' letter from the filter
     *  string 'state' state from the filter
     *
     * @return RedirectResponse
     */
    public function upgradeAction(Request $request, $id)
    {
        $csrftoken = $request->get('csrftoken');
        $this->checkCsrfToken($csrftoken);

        $startnum = (int) $request->query->get('startnum', null);
        $letter = $request->query->get('letter', null);
        $state = $request->query->get('state', null);

        // Upgrade module
        $res = (bool) ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'upgrade', array('id' => $id));

        if ($res) {
            // Success
            $request->getSession()->getFlashBag()->add('status', $this->__('New version'));
            if (ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'setstate',
                                 array('id' => $id,
                                       'state' => ModUtil::STATE_ACTIVE))) {
                // Success
                $request->getSession()->getFlashBag()->add('status', $this->__('Activated'));
            }

            // Clear the Zikula_View cached/compiled files and Themes cached/compiled/cssjs combination files
            $theme = Zikula_View_Theme::getInstance();
            $theme->clear_compiled();
            $theme->clear_all_cache();
            $theme->clear_cssjscombinecache();

            $this->view->clear_compiled();
            $this->view->clear_all_cache();
            $this->get('zikula.cache_clearer')->clear('symfony.routing');

            return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_view', array(
                    'startnum' => $startnum,
                    'letter' => $letter,
                    'state' => $state), RouterInterface::ABSOLUTE_URL));
        } else {
            $request->getSession()->getFlashBag()->add('error', $this->__('Extension upgrade failed!'));

            return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_view', array(
                    'startnum' => $startnum,
                    'letter' => $letter,
                    'state' => $state), RouterInterface::ABSOLUTE_URL));
        }
    }

    /**
     * @Route("/modules/deactivate/{id}", requirements={"id" = "^[1-9]\d*$"})
     *
     * Deactivate a module
     *
     * @param Request $request
     * @param int 'id' module id
     *
     *  int 'startnum' starting number from the pager
     *  string 'letter' letter from the filter
     *  string 'state' state from the filter
     *
     * @return RedirectResponse
     *
     * @throws NotFoundHttpException Thrown if the requested module id doesn't exist
     */
    public function deactivateAction(Request $request, $id)
    {
        $csrftoken = $request->get('csrftoken');
        $this->checkCsrfToken($csrftoken);

        $startnum = (int) $request->query->get('startnum', null);
        $letter = $request->query->get('letter', null);
        $state = $request->query->get('state', null);

        // check if the modules is the systems start module
        $modinfo = ModUtil::getInfo($id);
        if ($modinfo == false) {
            throw new NotFoundHttpException($this->__('Error! No such module ID exists.'));
        }

        if (ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'iscoremodule', array('modulename' => $modinfo['name']))) {
            $request->getSession()->getFlashBag()->add('error', $this->__f('Error! You cannot deactivate this module [%s]. It is a mandatory core module, and is needed by the system.', $modinfo['name']));
        } else {
            // Update state
            $setstate = ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'setstate', array('id' => $id, 'state' => ModUtil::STATE_INACTIVE));
            if ($setstate) {
                // Success
                $event = new GenericEvent(null, $modinfo);
                $this->getDispatcher()->dispatch('installer.module.deactivated', $event);
                $this->get('zikula.cache_clearer')->clear('symfony.routing');
                $request->getSession()->getFlashBag()->add('status', $this->__('Done! Deactivated module.'));
            }
        }

        return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_view', array(
                'startnum' => $startnum,
                'letter' => $letter,
                'state' => $state), RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/modules/remove")
     *
     * Remove a module
     *
     * @param Request $request
     *
     *  int 'id' module id
     *  int 'startnum' starting number from the pager
     *  string 'letter' letter from the filter
     *  string 'state' state from the filter
     *  boolean 'confirmation' if the user has confirmed the request
     *  array 'dependents'
     *
     * @return Response
     *
     * @throws \InvalidArgumentException Thrown if the id parameter is not provided or not numeric
     */
    public function removeAction(Request $request)
    {
        // Get parameters from whatever input we need
        $id = (int) $request->get('id', 0);
        $confirmation = (bool) $request->get('confirmation', false);
        $dependents = (array) $request->get('dependents');
        $startnum = (int) $request->get('startnum');
        $letter = $request->get('letter');
        $state = $request->get('state');

        if (empty($id) || !is_numeric($id) || !ModUtil::getInfo($id)) {
            throw new \InvalidArgumentException($this->__('Error! No module ID provided.'));
        }

        $modinfo = ModUtil::getInfo($id);
        if ($modinfo['state'] == ModUtil::STATE_MISSING) {
            // The module's files are missing. Deny uninstalling it.
            throw new \RuntimeException($this->__("Error! The requested module cannot be uninstalled as it's files are missing!"));
        }

        // Check for confirmation.
        if (empty($confirmation)) {
            // No confirmation yet
            // Add a hidden field for the item ID to the output
            $this->view->assign('id', $id);

            // assign any dependencies - filtering out non-active module dependents
            $dependents = ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'getdependents', array(
                    'modid' => $id));
            foreach ($dependents as $key => $dependent) {
                $modinfo = ModUtil::getInfo($dependent['modid']);
                if (!ModUtil::available($modinfo['name'])) {
                    unset($dependents[$key]);
                } else {
                    $dependents[$key] = array_merge($dependents[$key]->toArray(), $modinfo);
                }
            }

            // check the blocks module for existing blocks
            $blocks = ModUtil::apiFunc('ZikulaBlocksModule', 'user', 'getall', array(
                    'modid' => $id));
            $this->view->assign('hasBlocks', count($blocks));

            $this->view->assign('dependents', $dependents)
                       ->assign('startnum', $startnum)
                       ->assign('letter', $letter)
                       ->assign('state', $state);

            // Return the output that has been generated by this function
            return new Response($this->view->fetch('Admin/remove.tpl'));
        }

        // If we get here it means that the user has confirmed the action

        $csrftoken = $request->get('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // remove dependent modules
        foreach ($dependents as $dependent) {
            if (!ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'remove', array(
            'id' => $dependent))) {
                return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_view', array(
                        'startnum' => $startnum,
                        'letter' => $letter,
                        'state' => $state), RouterInterface::ABSOLUTE_URL));
            }
        }

        // remove the module blocks
        $blocks = ModUtil::apiFunc('ZikulaBlocksModule', 'user', 'getall', array(
                'modid' => $id));
        foreach ($blocks as $block) {
            if (!ModUtil::apiFunc('ZikulaBlocksModule', 'admin', 'delete', array(
            'bid' => $block['bid']))) {
                $request->getSession()->getFlashBag()->add('error', $this->__f('Error! Could not delete the block %s .', $block['title']));
            }
        }

        // Now we've removed dependents and associated blocks remove the main module
        $res = (bool) ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'remove', array('id' => $id));
        if ($res) {
            // Success
            $request->getSession()->getFlashBag()->add('status', $this->__('Done! Uninstalled module.'));

            return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_view', array(
                    'startnum' => $startnum,
                    'letter' => $letter,
                    'state' => $state), RouterInterface::ABSOLUTE_URL));
        } else {
            $request->getSession()->getFlashBag()->add('error', $this->__('Extension removal failed!'));

            return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_view', array(
                    'startnum' => $startnum,
                    'letter' => $letter,
                    'state' => $state), RouterInterface::ABSOLUTE_URL));
        }
    }

    /**
     * @Route("/config")
     * @Method("GET")
     *
     * This is a standard function to modify the configuration parameters of the module
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission to the module
     */
    public function modifyconfigAction()
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // assign all the module vars and return output
        return new Response($this->view->assign($this->getVars())
                          ->fetch('Admin/modifyconfig.tpl'));
    }

    /**
     * @Route("/config")
     * @Method("POST")
     *
     * This is a standard function to update the configuration parameters of the
     * module given the information passed back by the modification form
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission to the module
     */
    public function updateconfigAction(Request $request)
    {
        $this->checkCsrfToken();

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Update module variables.
        $itemsperpage = (int) $request->request->get('itemsperpage', 25);
        if (!is_int($itemsperpage) || $itemsperpage < 1) {
            $itemsperpage = abs($itemsperpage);
            $request->getSession()->getFlashBag()->add('warning', $this->__("Warning! The 'Items per page' setting must be a positive integer. The value you entered was corrected."));
        }

        $this->setVar('itemsperpage', $itemsperpage);

        // the module configuration has been updated successfuly
        $request->getSession()->getFlashBag()->add('status', $this->__('Done! Saved module configuration.'));

        // This function generated no output, and so now it is complete we redirect
        // the user to an appropriate page for them to carry on their work
        return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_view', array(), RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/modules/compatibility/{id}", requirements={"id" = "^[1-9]\d*$"})
     * @Method("GET")
     *
     * Display information of a module compatibility with the version of the core
     *
     * @param Request $request
     * @param  int 'id' identity of the module
     *
     * @return Response symfony response object
     *
     * @throws NotFoundHttpException Thrown if the requested module id doesn't exist
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission to the requested module
     */
    public function compinfoAction(Request $request, $id)
    {
        // get our input
        $startnum = (int) $request->get('startnum');
        $letter = $request->get('letter');
        $state = (int) $request->get('state');

        // get the modules information from the data base
        $modinfo = ModUtil::getInfo($id);
        if ($modinfo == false) {
            throw new NotFoundHttpException($this->__('Error! No such module ID exists.'));
        }

        if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', "$modinfo[name]::$id", ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // get the module information from the files system
        $moduleInfo = ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'getfilemodules');

        // assign the module information and other variables to the template
        $this->view->assign('moduleInfo', $moduleInfo[($modinfo['name'])])
                   ->assign('id', $id)
                   ->assign('startnum', $startnum)
                   ->assign('letter', $letter)
                   ->assign('state', $state);

        // Return the output that has been generated by this function
        return new Response($this->view->fetch('Admin/compinfo.tpl'));
    }

    /**
     * @Route("/plugins")
     *
     * Lists all plugins.
     *
     * @param Request $request
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission to the module
     */
    public function viewPluginsAction(Request $request)
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $state = $request->get('state', -1);
        $sort = $request->get('sort', null);
        $module = $request->get('bymodule', null);
        $systemplugins = $request->get('systemplugins', false) ? true : null;

        $this->view->assign('state', $state);

        // generate an auth key to use in urls
        $csrfToken = SecurityUtil::generateCsrfToken($this->getContainer(), true);
        $plugins = array();
        $pluginClasses = ($systemplugins) ? PluginUtil::loadAllSystemPlugins() : PluginUtil::loadAllModulePlugins();

        foreach ($pluginClasses as $className) {
            $instance = PluginUtil::loadPlugin($className);
            $pluginstate = PluginUtil::getState($instance->getServiceId(), PluginUtil::getDefaultState());

            // Tweak UI if the plugin is AlwaysOn
            if ($instance instanceof Zikula_Plugin_AlwaysOnInterface) {
                $pluginstate['state'] = PluginUtil::ENABLED;
                $pluginstate['version'] = $instance->getMetaVersion();
            }

            // state filer
            if ($state >= 0 && $pluginstate['state'] != $state) {
                continue;
            }

            // module filter
            if (!empty($module) && $instance->getModuleName() != $module) {
                continue;
            }

            $actions = array();
            // Translate state
            switch ($pluginstate['state']) {
                case PluginUtil::NOTINSTALLED:
                    $status = $this->__('Not installed');
                    $statusclass = "danger";

                    $actions[] = array('url' => $this->get('router')->generate('zikulaextensionsmodule_admin_initialiseplugin',
                                                    array('plugin' => $className,
                                                          'state'  => $state,
                                                          'bymodule' => $module,
                                                          'sort'   => $sort,
                                                          'systemplugins' => $systemplugins,
                                                          'csrftoken' => $csrfToken)
                                                ),
                                       'image' => 'cog fa-lg text-success',
                                       'color' => '#0c0',
                                       'title' => $this->__('Install'));
                    break;
                case PluginUtil::ENABLED:
                    $status = $this->__('Active');
                    $statusclass = "success";
                    $pluginLink = array();
                    if (!$systemplugins) {
                        $pluginLink['_module'] = $instance->getModuleName();
                    }
                    $pluginLink['_plugin'] = $instance->getPluginName();
                    $pluginLink['_action'] = 'configure';

                    if ($instance instanceof Zikula_Plugin_ConfigurableInterface) {
                        $actions[] = array('url' => $this->get('router')->generate('zikulaextensionsmodule_adminplugin_dispatch', $pluginLink),
                                           'image' => 'wrench fa-lg',
                                           'color' => '#111',
                                           'title' => $this->__('Configure plugin'));
                    }

                    // Dont allow to disable/uninstall plugins that are AlwaysOn
                    if (!$instance instanceof Zikula_Plugin_AlwaysOnInterface) {
                        $actions[] = array('url' => $this->get('router')->generate('zikulaextensionsmodule_admin_deactivateplugin',
                                                    array('plugin' => $className,
                                                          'state'  => $state,
                                                          'bymodule' => $module,
                                                          'sort'   => $sort,
                                                          'systemplugins' => $systemplugins,
                                                          'csrftoken' => $csrfToken)
                                                ),
                                       'image' => 'minus-circle fa-lg text-danger',
                                       'color' => '#c00',
                                       'title' => $this->__('Deactivate'));

                        $actions[] = array('url' => $this->get('router')->generate('zikulaextensionsmodule_admin_removeplugin',
                                                    array('plugin' => $className,
                                                          'state'  => $state,
                                                          'bymodule' => $module,
                                                          'sort'   => $sort,
                                                          'systemplugins' => $systemplugins,
                                                          'csrftoken' => $csrfToken)
                                                ),
                                       'image' => 'trash-o fa-lg',
                                       'color' => '#c00',
                                       'title' => $this->__('Remove plugin'));
                    }
                    break;
                case PluginUtil::DISABLED:
                    $status = $this->__('Inactive');
                    $statusclass = "warning";

                    $actions[] = array('url' => $this->get('router')->generate('zikulaextensionsmodule_admin_activateplugin',
                                                    array('plugin' => $className,
                                                          'state'  => $state,
                                                          'bymodule' => $module,
                                                          'sort'   => $sort,
                                                          'systemplugins' => $systemplugins,
                                                          'csrftoken' => $csrfToken)
                                                ),
                                       'image' => 'plus-square fa-lg text-success',
                                       'color' => '#0c0',
                                       'title' => $this->__('Activate'));

                    $actions[] = array('url' => $this->get('router')->generate('zikulaextensionsmodule_admin_removeplugin',
                                                    array('plugin' => $className,
                                                           'state' => $state,
                                                           'bymodule' => $module,
                                                           'sort'   => $sort,
                                                           'systemplugins' => $systemplugins,
                                                           'csrftoken' => $csrfToken)
                                                ),
                                       'image' => 'trash-o fa-lg',
                                       'color' => '#c00',
                                       'title' => $this->__('Remove plugin'));

                    break;
            }

            // upgrade ?
            if ($pluginstate['state'] != PluginUtil::NOTINSTALLED
                && $pluginstate['version'] != $instance->getMetaVersion()) {
                $status = $this->__('New version');
                $statusclass = "danger";

                $actions = array();
                $actions[] = array('url' => $this->get('router')->generate('zikulaextensionsmodule_admin_upgradeplugin',
                                                array('plugin' => $className,
                                                      'state'  => $state,
                                                      'bymodule' => $module,
                                                      'sort'   => $sort,
                                                      'systemplugins' => $systemplugins,
                                                      'csrftoken' => $csrfToken)),
                                    'image' => 'refresh fa-lg',
                                    'color' => '#00c',
                                    'title' => $this->__('Upgrade'));

                $actions[] = array('url' => $this->get('router')->generate('zikulaextensionsmodule_admin_removeplugin',
                                                array('plugin' => $className,
                                                       'state' => $state,
                                                       'bymodule' => $module,
                                                       'sort'   => $sort,
                                                       'systemplugins' => $systemplugins,
                                                       'csrftoken' => $csrfToken)),
                                    'image' => 'trash-o fa-lg',
                                    'color' => '#c00',
                                    'title' => $this->__('Remove plugin'));
            }

            $info =  array('instance'    => $instance,
                           'status'      => $status,
                           'statusclass' => $statusclass,
                           'actions'     => $actions,
                           'version'     => $pluginstate['state'] == PluginUtil::NOTINSTALLED ?
                                                 $instance->getMetaVersion() : $pluginstate['version']);

            // new version of plugin?
            if ($pluginstate['state'] != PluginUtil::NOTINSTALLED
                && $pluginstate['version'] != $instance->getMetaVersion()) {
                $info['newversion'] = $instance->getMetaVersion();
            }

            $plugins[] = $info;
        }

        // sort plugins array
        if (empty($sort) || $sort == 'module') {
            usort($plugins, array($this, 'viewPluginsSorter_byModule'));
        } elseif ($sort == 'name') {
            usort($plugins, array($this, 'viewPluginsSorter_byName'));
        }

        $this->view->assign('plugins', $plugins)
                   ->assign('module', $module)
                   ->assign('sort', $sort)
                   ->assign('state', $state)
                   ->assign('systemplugins', $systemplugins)
                   ->assign('_type', ($systemplugins) ? 'system' : 'module');

        // Return the output that has been generated by this function
        return new Response($this->view->fetch('Admin/viewPlugins.tpl'));
    }

    /**
     * @Route("/plugins/initialize/{plugin}")
     *
     * Initialise a plugin
     *
     * @param Request $request
     * @param string $plugin   The plugin class
     *
     *  int    $state    The state filter
     *  string $sort     The sort order
     *  string $bymodule The bymodule filter
     *  string $systemplugins
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission to the module
     */
    public function initialisePluginAction(Request $request, $plugin)
    {
        $csrftoken = $request->get('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // Security and sanity checks
        if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Get parameters from whatever input we need
        $state = $request->get('state', -1);
        $sort = $request->get('sort', null);
        $module = $request->get('bymodule', null);
        $systemplugins = $request->get('systemplugins', false) ? true : null;

        PluginUtil::loadAllPlugins();
        if (PluginUtil::install($plugin)) {
            $request->getSession()->getFlashBag()->add('status', $this->__('Done! Installed plugin.'));
        }

        return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_viewplugins', array('state' => $state,
                                                                              'sort'  => $sort,
                                                                              'bymodule' => $module,
                                                                              'systemplugins' => $systemplugins), RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/plugins/deactivate/{plugin}")
     *
     * Deactivate a plugin
     *
     * @param Request $request
     * @param string $plugin   The plugin class
     *
     *  int    $state    The state filter
     *  string $sort     The sort order
     *  string $bymodule The bymodule filter
     *  string $systemplugins
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission to the module
     */
    public function deactivatePluginAction(Request $request, $plugin)
    {
        $csrftoken = $request->query->get('csrftoken', false);
        $this->checkCsrfToken($csrftoken);

        // Security and sanity checks
        if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Get parameters from whatever input we need
        $state = $request->get('state', -1);
        $sort = $request->get('sort', null);
        $module = $request->get('bymodule', null);
        $systemplugins = $request->get('systemplugins', false) ? true : null;

        PluginUtil::loadAllPlugins();
        if (PluginUtil::disable($plugin)) {
            $request->getSession()->getFlashBag()->add('status', $this->__('Done! Deactivated plugin.'));
        }

        return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_viewplugins', array('state' => $state,
                                                                              'sort'  => $sort,
                                                                              'bymodule' => $module,
                                                                              'systemplugins' => $systemplugins), RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/plugins/activate/{plugin}")
     *
     * Activate a plugin
     *
     * @param Request $request
     * @param string $plugin   The plugin class
     *
     *  int    $state    The state filter
     *  string $sort     The sort order
     *  string $bymodule The bymodule filter
     *  string $systemplugins
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission to the module
     */
    public function activatePluginAction(Request $request, $plugin)
    {
        $csrftoken = $request->query->get('csrftoken', false);
        $this->checkCsrfToken($csrftoken);

        // Security and sanity checks
        if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Get parameters from whatever input we need
        $state = $request->get('state', -1);
        $sort = $request->get('sort', null);
        $module = $request->get('bymodule', null);
        $systemplugins = $request->get('systemplugins', false) ? true : null;

        PluginUtil::loadAllPlugins();
        if (PluginUtil::enable($plugin)) {
            $request->getSession()->getFlashBag()->add('status', $this->__('Done! Activated plugin.'));
        }

        return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_viewplugins', array('state' => $state,
                                                                              'sort'  => $sort,
                                                                              'bymodule' => $module,
                                                                              'systemplugins' => $systemplugins), RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/plugins/remove/{plugin}")
     *
     * Remove a plugin
     *
     * @param Request $request
     * @param string $plugin   The plugin class
     *
     *  int    $state    The state filter
     *  string $sort     The sort order
     *  string $bymodule The bymodule filter
     *  string $systemplugins
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission to the module
     */
    public function removePluginAction(Request $request, $plugin)
    {
        $csrftoken = $request->get('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // Security and sanity checks
        if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Get parameters from whatever input we need
        $state = $request->get('state', -1);
        $sort = $request->get('sort', null);
        $module = $request->get('bymodule', null);
        $systemplugins = $request->get('systemplugins', false) ? true : null;

        PluginUtil::loadAllPlugins();
        if (PluginUtil::uninstall($plugin)) {
            $request->getSession()->getFlashBag()->add('status', $this->__('Done! De-installed plugin.'));
        }

        return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_viewplugins', array('state' => $state,
                                                                              'sort'  => $sort,
                                                                              'bymodule' => $module,
                                                                              'systemplugins' => $systemplugins), RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/plugins/upgrade/{plugin}")
     *
     * Upgrade a plugin
     *
     * @param Request $request
     * @param string $plugin   The plugin class
     *
     *  int    $state    The state filter
     *  string $sort     The sort order
     *  string $bymodule The bymodule filter
     *  string $systemplugins
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission to the module
     */
    public function upgradePluginAction(Request $request, $plugin)
    {
        $csrftoken = $request->get('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // Security and sanity checks
        if (!SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Get parameters from whatever input we need
        $state = $request->get('state', -1);
        $sort = $request->get('sort', null);
        $module = $request->get('bymodule', null);
        $systemplugins = $request->get('systemplugins', false) ? true : null;

        PluginUtil::loadAllPlugins();
        if (PluginUtil::upgrade($plugin)) {
            $request->getSession()->getFlashBag()->add('status', $this->__('Done! Upgraded plugin.'));
        }

        return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_viewplugins', array('state' => $state,
                                                                              'sort'  => $sort,
                                                                              'bymodule' => $module,
                                                                              'systemplugins' => $systemplugins), RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/modules/upgradeall")
     *
     * Upgrade all modules
     *
     * @return RedirectResponse
     */
    public function upgradeallAction()
    {
        ModUtil::apiFunc('ZikulaExtensionsModule', 'admin', 'upgradeall');

        return new RedirectResponse($this->get('router')->generate('zikulaextensionsmodule_admin_view', array(), RouterInterface::ABSOLUTE_URL));
    }

    /**
     * viewPlugins sorter: Sorting by module name
     *
     * @param $a array first item to compare 
     * @param $b array second item to compare
     *
     * @return int < 0 if plugin a should be ordered before module b > 0 otherwise
     */
    private function viewPluginsSorter_byModule($a, $b)
    {
        return strcmp($a['instance']->getModuleName(), $b['instance']->getModuleName());
    }

    /**
     * viewPlugins sorter: Sorting by plugin internal name
     *
     * @param $a array first item to compare 
     * @param $b array second item to compare
     *
     * @return int < 0 if plugin a should be ordered before module b > 0 otherwise
     */
    private function viewPluginsSorter_byName($a, $b)
    {
        return strcmp($a['instance']->getPluginName(), $b['instance']->getPluginName());
    }

    /**
     * @Route("/hooks/{moduleName}", options={"zkNoBundlePrefix" = 1})
     * @Method("GET")
     *
     * Display hooks user interface
     *
     * @return Response
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions over the module
     * @internal param $moduleName
     */
    public function hooksAction($moduleName)
    {
        // get module's name and assign it to template
        $this->view->assign('currentmodule', $moduleName);

        // check if user has admin permission on this module
        if (!SecurityUtil::checkPermission($moduleName.'::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // create an instance of the module's version
        // we will use it to get the bundles
        $moduleVersionObj = ExtensionsUtil::getVersionMeta($moduleName);
        if ($moduleVersionObj instanceof MetaData) {
            // Core-2.0 Spec module
            $moduleVersionObj = $this->get('zikula_extensions_module.api.hook')->getHookContainerInstance($moduleVersionObj);
        }

        // find out the capabilities of the module
        $isProvider = (HookUtil::isProviderCapable($moduleName)) ? true : false;
        $this->view->assign('isProvider', $isProvider);

        $isSubscriber = (HookUtil::isSubscriberCapable($moduleName)) ? true : false;
        $this->view->assign('isSubscriber', $isSubscriber);

        $isSubscriberSelfCapable = (HookUtil::isSubscriberSelfCapable($moduleName)) ? true : false;
        $this->view->assign('isSubscriberSelfCapable', $isSubscriberSelfCapable);

        // get areas of module and bundle titles also
        if ($isProvider) {
            $providerAreas = HookUtil::getProviderAreasByOwner($moduleName);
            $this->view->assign('providerAreas', $providerAreas);

            $providerAreasToTitles = array();
            foreach ($providerAreas as $providerArea) {
                $providerAreasToTitles[$providerArea] = $this->view->__(/** @Ignore */$moduleVersionObj->getHookProviderBundle($providerArea)->getTitle());
            }
            $this->view->assign('providerAreasToTitles', $providerAreasToTitles);
        }

        if ($isSubscriber) {
            $subscriberAreas = HookUtil::getSubscriberAreasByOwner($moduleName);
            $this->view->assign('subscriberAreas', $subscriberAreas);

            $subscriberAreasToTitles = array();
            foreach ($subscriberAreas as $subscriberArea) {
                $subscriberAreasToTitles[$subscriberArea] = $this->view->__(/** @Ignore */$moduleVersionObj->getHookSubscriberBundle($subscriberArea)->getTitle());
            }
            $this->view->assign('subscriberAreasToTitles', $subscriberAreasToTitles);

            $subscriberAreasToCategories = array();
            foreach ($subscriberAreas as $subscriberArea) {
                $category = $this->view->__(/** @Ignore */$moduleVersionObj->getHookSubscriberBundle($subscriberArea)->getCategory());
                $subscriberAreasToCategories[$subscriberArea] = $category;
            }
            $this->view->assign('subscriberAreasToCategories', $subscriberAreasToCategories);

            $subscriberAreasAndCategories = array();
            foreach ($subscriberAreas as $subscriberArea) {
                $category = $this->view->__(/** @Ignore */$moduleVersionObj->getHookSubscriberBundle($subscriberArea)->getCategory());
                $subscriberAreasAndCategories[$category][] = $subscriberArea;
            }
            $this->view->assign('subscriberAreasAndCategories', $subscriberAreasAndCategories);
        }

        // get available subscribers that can attach to provider
        if ($isProvider && !empty($providerAreas)) {
            $hooksubscribers = HookUtil::getHookSubscribers();
            $total_hooksubscribers = count($hooksubscribers);
            $total_available_subscriber_areas = 0;
            for ($i = 0; $i < $total_hooksubscribers; $i++) {
                // don't allow subscriber and provider to be the same
                // unless subscriber has the ability to connect to it's own providers
                if ($hooksubscribers[$i]['name'] == $moduleName) {
                    unset($hooksubscribers[$i]);
                    continue;
                }
                // does the user have admin permissions on the subscriber module?
                if (!SecurityUtil::checkPermission($hooksubscribers[$i]['name']."::", '::', ACCESS_ADMIN)) {
                    unset($hooksubscribers[$i]);
                    continue;
                }

                // create an instance of the subscriber's version
                $hooksubscriberVersionObj = ExtensionsUtil::getVersionMeta($hooksubscribers[$i]['name']);
                if ($hooksubscriberVersionObj instanceof MetaData) {
                    // Core-2.0 Spec module
                    $hooksubscriberVersionObj = $this->get('zikula_extensions_module.api.hook')->getHookContainerInstance($hooksubscriberVersionObj);
                }

                // get the areas of the subscriber
                $hooksubscriberAreas = HookUtil::getSubscriberAreasByOwner($hooksubscribers[$i]['name']);
                $hooksubscribers[$i]['areas'] = $hooksubscriberAreas;
                $total_available_subscriber_areas += count($hooksubscriberAreas);

                // and get the titles
                $hooksubscriberAreasToTitles = array();
                foreach ($hooksubscriberAreas as $hooksubscriberArea) {
                    $hooksubscriberAreasToTitles[$hooksubscriberArea] = $this->view->__(/** @Ignore */$hooksubscriberVersionObj->getHookSubscriberBundle($hooksubscriberArea)->getTitle());
                }
                $hooksubscribers[$i]['areasToTitles'] = $hooksubscriberAreasToTitles;

                // and get the categories
                $hooksubscriberAreasToCategories = array();
                foreach ($hooksubscriberAreas as $hooksubscriberArea) {
                    $category = $this->view->__(/** @Ignore */$hooksubscriberVersionObj->getHookSubscriberBundle($hooksubscriberArea)->getCategory());
                    $hooksubscriberAreasToCategories[$hooksubscriberArea] = $category;
                }
                $hooksubscribers[$i]['areasToCategories'] = $hooksubscriberAreasToCategories;
            }
            $this->view->assign('hooksubscribers', $hooksubscribers);
            $this->view->assign('total_available_subscriber_areas', $total_available_subscriber_areas);
        } else {
            $this->view->assign('total_available_subscriber_areas', 0);
        }

        // get providers that are already attached to the subscriber
        // and providers that can attach to the subscriber
        if ($isSubscriber && !empty($subscriberAreas)) {
            // get current sorting
            $currentSortingTitles = array();
            $currentSorting = array();
            $total_attached_provider_areas = 0;
            for ($i = 0; $i < count($subscriberAreas); $i++) {
                $sortsByArea = HookUtil::getBindingsFor($subscriberAreas[$i]);
                foreach ($sortsByArea as $sba) {
                    $areaname = $sba['areaname'];
                    $category = $sba['category'];

                    if (!isset($currentSorting[$category])) {
                        $currentSorting[$category] = array();
                    }

                    if (!isset($currentSorting[$category][$subscriberAreas[$i]])) {
                        $currentSorting[$category][$subscriberAreas[$i]] = array();
                    }

                    array_push($currentSorting[$category][$subscriberAreas[$i]], $areaname);
                    $total_attached_provider_areas++;

                    // get hook provider from it's area
                    $sbaProviderModule = HookUtil::getOwnerByArea($areaname);

                    // create an instance of the provider's version
                    $sbaProviderModuleVersionObj = ExtensionsUtil::getVersionMeta($sbaProviderModule);
                    if ($sbaProviderModuleVersionObj instanceof MetaData) {
                        // Core-2.0 Spec module
                        $sbaProviderModuleVersionObj = $this->get('zikula_extensions_module.api.hook')->getHookContainerInstance($sbaProviderModuleVersionObj);
                    }

                    // get the bundle title
                    $currentSortingTitles[$areaname] = $this->view->__(/** @Ignore */$sbaProviderModuleVersionObj->getHookProviderBundle($areaname)->getTitle());
                }
            }
            $this->view->assign('areasSorting', $currentSorting);
            $this->view->assign('areasSortingTitles', $currentSortingTitles);
            $this->view->assign('total_attached_provider_areas', $total_attached_provider_areas);

            // get available providers
            $hookproviders = HookUtil::getHookProviders();
            $total_hookproviders = count($hookproviders);
            $total_available_provider_areas = 0;
            for ($i = 0; $i < $total_hookproviders; $i++) {
                // don't allow subscriber and provider to be the same
                // unless subscriber has the ability to connect to it's own providers
                if ($hookproviders[$i]['name'] == $moduleName && !$isSubscriberSelfCapable) {
                    unset($hookproviders[$i]);
                    continue;
                }

                // does the user have admin permissions on the provider module?
                if (!SecurityUtil::checkPermission($hookproviders[$i]['name']."::", '::', ACCESS_ADMIN)) {
                    unset($hookproviders[$i]);
                    continue;
                }

                // create an instance of the provider's version
                $hookproviderVersionObj = ExtensionsUtil::getVersionMeta($hookproviders[$i]['name']);
                if ($hookproviderVersionObj instanceof MetaData) {
                    // Core-2.0 Spec module
                    $hookproviderVersionObj = $this->get('zikula_extensions_module.api.hook')->getHookContainerInstance($hookproviderVersionObj);
                }

                // get the areas of the provider
                $hookproviderAreas = HookUtil::getProviderAreasByOwner($hookproviders[$i]['name']);
                $hookproviders[$i]['areas'] = $hookproviderAreas;
                $total_available_provider_areas += count($hookproviderAreas);

                // and get the titles
                $hookproviderAreasToTitles = array();
                foreach ($hookproviderAreas as $hookproviderArea) {
                    $hookproviderAreasToTitles[$hookproviderArea] = $this->view->__(/** @Ignore */$hookproviderVersionObj->getHookProviderBundle($hookproviderArea)->getTitle());
                }
                $hookproviders[$i]['areasToTitles'] = $hookproviderAreasToTitles;

                // and get the categories
                $hookproviderAreasToCategories = array();
                foreach ($hookproviderAreas as $hookproviderArea) {
                    $hookproviderAreasToCategories[$hookproviderArea] = $this->view->__(/** @Ignore */$hookproviderVersionObj->getHookProviderBundle($hookproviderArea)->getCategory());
                }
                $hookproviders[$i]['areasToCategories'] = $hookproviderAreasToCategories;

                // and build array with category => areas
                $hookproviderAreasAndCategories = array();
                foreach ($hookproviderAreas as $hookproviderArea) {
                    $category = $this->view->__(/** @Ignore */$hookproviderVersionObj->getHookProviderBundle($hookproviderArea)->getCategory());
                    $hookproviderAreasAndCategories[$category][] = $hookproviderArea;
                }
                $hookproviders[$i]['areasAndCategories'] = $hookproviderAreasAndCategories;
            }
            $this->view->assign('hookproviders', $hookproviders);
            $this->view->assign('total_available_provider_areas', $total_available_provider_areas);
        } else {
            $this->view->assign('hookproviders', array());
        }

        return new Response($this->view->fetch('Admin/HookUi/hooks.tpl'));
    }

    /**
     * @Route("/moduleservices/{moduleName}", options={"zkNoBundlePrefix" = 1})
     * @Method("GET")
     *
     * Display services available to the module
     *
     * @param $moduleName
     * @internal param GenericEvent $event
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions over the module
     *
     * @return Response
     */
    public function moduleServicesAction($moduleName)
    {
        if (!SecurityUtil::checkPermission($moduleName.'::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // notify EVENT here to gather any system service links
        $event = new GenericEvent(null, array('modname' => $moduleName));
        \EventUtil::dispatch('module_dispatch.service_links', $event);
        $sublinks = $event->getData();
        $this->view->assign('sublinks', $sublinks);
        $this->view->assign('currentmodule', $moduleName);

        return new Response($this->view->fetch('Admin/HookUi/moduleservices.tpl'));
    }

    /**
     * compute if bundle requirements are met
     *
     * @param array $dependency
     * @return bool
     */
    private function bundleDependencySatisfied(array &$dependency)
    {
        if ($dependency['modname'] == "php") {
            $phpVersion = new version(PHP_VERSION);
            $requiredVersionExpression = new expression($dependency['minversion']);

            return $requiredVersionExpression->satisfiedBy($phpVersion);
        }
        if (strpos($dependency['modname'], 'composer/') !== false) {
            // @todo this specifically is for `composer/installers` but will catch all with `composer/`
            return true;
        }
        if ($dependency['minversion'] == "-1") {
            // dependency is "suggested"
            list($dependency['modname'], $dependency['minversion']) = explode(':', $dependency['modname']);

            return false;
        }
        if (strpos($dependency['modname'], '/') !== false) {
            if ($this->get('kernel')->isBundle($dependency['modname'])) {
                if (empty($this->installedPackages)) {
                    // create and cache installed packages from composer.lock file
                    $appPath = $this->get('kernel')->getRootDir();
                    $composerLockPath = realpath($appPath . '/../') . 'composer.lock';
                    $packages = json_decode(file_get_contents($composerLockPath), true);
                    foreach ($packages as $package) {
                        $this->installedPackages[$package['name']] = $package;
                    }
                }
                $bundleVersion = new version($this->installedPackages[$dependency['modname']]['version']);
                $requiredVersionExpression = new expression($dependency['minversion']);

                return $requiredVersionExpression->satisfiedBy($bundleVersion);
            }

            $dependency['reason'] = $this->__('This dependency can only be resolved via `composer update`');

            return false;
        }

        return false;
    }
}
