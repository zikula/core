<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Controller;

use Zikula_View;
use ModUtil;
use SecurityUtil;
use System;
use Zikula_Core;
use CacheUtil;
use DataUtil;
use UserUtil;
use Zikula\SecurityCenterModule\Util as SecurityCenterUtil;
use HTMLPurifier;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Symfony\Component\Routing\RouterInterface;

/**
 * @Route("/admin")
 *
 * Administrative controllers for the security centre module
 */
class AdminController extends \Zikula_AbstractController
{
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
     * The main administration function.
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        // Security check will be done in modifyconfig()
        return new RedirectResponse($this->get('router')->generate('zikulasecuritycentermodule_admin_modifyconfig', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * Route not needed here because method is legacy-only
     *
     * The main administration function.
     *
     * @deprecated since 1.4.0 use indexAction instead
     *
     * @return RedirectResponse
     */
    public function mainAction()
    {
        // Security check will be done in modifyconfig()
        return new RedirectResponse($this->get('router')->generate('zikulasecuritycentermodule_admin_modifyconfig', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/config")
     * @Method("GET")
     *
     * This is a standard function to modify the configuration parameters of the module.
     *
     * @return Response symfony response object.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function modifyconfigAction()
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaSecurityCenterModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $this->view->assign('itemsperpage', $this->getVar('itemsperpage'));

        $this->view->assign('idshtmlfields', implode(PHP_EOL, System::getVar('idshtmlfields')));
        $this->view->assign('idsjsonfields', implode(PHP_EOL, System::getVar('idsjsonfields')));
        $this->view->assign('idsexceptions', implode(PHP_EOL, System::getVar('idsexceptions')));
        $this->view->assign('sessionname', $this->view->getContainer()->getParameter('zikula.session.name'));

        return new Response($this->view->fetch('Admin/modifyconfig.tpl'));
    }

    /**
     * @Route("/config")
     * @Method("POST")
     *
     * Update the configuration parameters of the module given the information passed back by the modification form
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function updateconfigAction(Request $request)
    {
        $this->checkCsrfToken();

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaSecurityCenterModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $validates = true;

        // Update module variables.
        $updatecheck = (int)$request->request->get('updatecheck', 0);
        System::setVar('updatecheck', $updatecheck);

        // if update checks are disabled, reset values to force new update check if re-enabled
        if ($updatecheck == 0) {
            System::setVar('updateversion', Zikula_Core::VERSION_NUM);
            System::setVar('updatelastchecked', 0);
        }

        $updatefrequency = (int)$request->request->get('updatefrequency', 30);
        System::setVar('updatefrequency', $updatefrequency);

        $keyexpiry = (int)$request->request->get('keyexpiry', 0);
        if ($keyexpiry < 0 || $keyexpiry > 3600) {
            $keyexpiry = 0;
        }
        System::setVar('keyexpiry', $keyexpiry);

        $sessionauthkeyua = (int)$request->request->get('sessionauthkeyua', 0);
        System::setVar('sessionauthkeyua', $sessionauthkeyua);

        $secure_domain = $request->request->get('secure_domain', '');
        System::setVar('secure_domain', $secure_domain);

        $signcookies = (int)$request->request->get('signcookies', 1);
        System::setVar('signcookies', $signcookies);

        $signingkey = $request->request->get('signingkey', '');
        System::setVar('signingkey', $signingkey);

        $seclevel = $request->request->get('seclevel', 'High');
        System::setVar('seclevel', $seclevel);

        $secmeddays = (int)$request->request->get('secmeddays', 7);
        if ($secmeddays < 1 || $secmeddays > 365) {
            $secmeddays = 7;
        }
        System::setVar('secmeddays', $secmeddays);

        $secinactivemins = (int)$request->request->get('secinactivemins', 20);
        if ($secinactivemins < 1 || $secinactivemins > 1440) {
            $secinactivemins = 7;
        }
        System::setVar('secinactivemins', $secinactivemins);

        $sessionstoretofile = (int)$request->request->get('sessionstoretofile', 0);
        $sessionsavepath = $request->request->get('sessionsavepath', '');

        // check session path config is writable (if method is being changed to session file storage)
        $cause_logout = false;
        $storeTypeCanBeWritten = true;
        if ($sessionstoretofile == 1 && !empty($sessionsavepath)) {
            // fix path on windows systems
            $sessionsavepath = str_replace('\\', '/', $sessionsavepath);
            // sanitize the path
            $sessionsavepath = trim(stripslashes($sessionsavepath));

            // check if sessionsavepath is a dir and if it is writable
            // if yes, we need to logout
            $cause_logout = (is_dir($sessionsavepath)) ? is_writable($sessionsavepath) : false;

            if ($cause_logout == false) {
                // an error occured - we do not change the way of storing session data
                $request->getSession()->getFlashBag()->add('error', $this->__('Error! Session path not writeable!'));
            }
        }
        if ($storeTypeCanBeWritten == true) {
            System::setVar('sessionstoretofile', $sessionstoretofile);
            System::setVar('sessionsavepath', $sessionsavepath);
        }

        if ((bool)$sessionstoretofile != (bool)System::getVar('sessionstoretofile')) {
            // logout if going from one storage to another one
            $cause_logout = true;
        }

        $gc_probability = (int)$request->request->get('gc_probability', 100);
        if ($gc_probability < 1 || $gc_probability > 10000) {
            $gc_probability = 7;
        }
        System::setVar('gc_probability', $gc_probability);

        $anonymoussessions = (int)$request->request->get('anonymoussessions', 1);
        System::setVar('anonymoussessions', $anonymoussessions);

        $sessionrandregenerate = (int)$request->request->get('sessionrandregenerate', 1);
        System::setVar('sessionrandregenerate', $sessionrandregenerate);

        $sessionregenerate = (int)$request->request->get('sessionregenerate', 1);
        System::setVar('sessionregenerate', $sessionregenerate);

        $sessionregeneratefreq = (int)$request->request->get('sessionregeneratefreq', 10);
        if ($sessionregeneratefreq < 1 || $sessionregeneratefreq > 100) {
            $sessionregeneratefreq = 10;
        }
        System::setVar('sessionregeneratefreq', $sessionregeneratefreq);

        $sessionipcheck = (int)$request->request->get('sessionipcheck', 0);
        System::setVar('sessionipcheck', $sessionipcheck);

        $sessionNameParameter = $this->view->getContainer()->getParameter('zikula.session.name');
        $sessionname = $request->request->get('sessionname', $sessionNameParameter);
        if (strlen($sessionname) < 3) {
            $sessionname = $sessionNameParameter;
        }

        $sessioncsrftokenonetime = (int)$request->request->get('sessioncsrftokenonetime', 0);
        System::setVar('sessioncsrftokenonetime', $sessioncsrftokenonetime);

        // cause logout if we changed session name
        if ($sessionname != System::getVar('sessionname')) {
            $cause_logout = true;
        }

        // set the session name in custom_parameters.yml
        $configDumper = $this->view->getContainer()->get('zikula.dynamic_config_dumper');
        $configDumper->setParameter('zikula.session.name', $sessionname);
        // set the session name in the current container
        $this->view->getContainer()->setParameter('zikula.session.name', $sessionname);
        System::setVar('sessionname', $sessionname);
        System::setVar('sessionstoretofile', $sessionstoretofile);

        $outputfilter = $request->request->get('outputfilter', 0);
        System::setVar('outputfilter', $outputfilter);

        $useids = (bool)$request->request->get('useids', 0);
        System::setVar('useids', $useids);

        // create tmp directory for PHPIDS
        if ($useids == 1) {
            $idsTmpDir = CacheUtil::getLocalDir() . '/idsTmp';
            if (!file_exists($idsTmpDir)) {
                CacheUtil::clearLocalDir('idsTmp');
            }
        }

        $idssoftblock = (bool)$request->request->get('idssoftblock', 1);
        System::setVar('idssoftblock', $idssoftblock);

        $idsmail = (bool)$request->request->get('idsmail', 1);
        System::setVar('idsmail', $idsmail);

        $idsfilter = $request->request->get('idsfilter', 'xml');
        System::setVar('idsfilter', $idsfilter);

        $idsrulepath = $request->request->get('idsrulepath', 'config/zikula_default.xml');
        $idsrulepath = DataUtil::formatForOS($idsrulepath);
        if (is_readable($idsrulepath)) {
            System::setVar('idsrulepath', $idsrulepath);
        } else {
            $request->getSession()->getFlashBag()->add('error', $this->__f('Error! PHPIDS rule file %s does not exist or is not readable.', $idsrulepath));
            $validates = false;
        }

        $idsimpactthresholdone = (int)$request->request->get('idsimpactthresholdone', 1);
        System::setVar('idsimpactthresholdone', $idsimpactthresholdone);

        $idsimpactthresholdtwo = (int)$request->request->get('idsimpactthresholdtwo', 10);
        System::setVar('idsimpactthresholdtwo', $idsimpactthresholdtwo);

        $idsimpactthresholdthree = (int)$request->request->get('idsimpactthresholdthree', 25);
        System::setVar('idsimpactthresholdthree', $idsimpactthresholdthree);

        $idsimpactthresholdfour = (int)$request->request->get('idsimpactthresholdfour', 75);
        System::setVar('idsimpactthresholdfour', $idsimpactthresholdfour);

        $idsimpactmode = (int)$request->request->get('idsimpactmode', 1);
        System::setVar('idsimpactmode', $idsimpactmode);

        $idshtmlfields = $request->request->get('idshtmlfields', '');
        $idshtmlfields = explode(PHP_EOL, $idshtmlfields);
        $idshtmlarray = [];
        foreach ($idshtmlfields as $idshtmlfield) {
            $idshtmlfield = trim($idshtmlfield);
            if (!empty($idshtmlfield)) {
                $idshtmlarray[] = $idshtmlfield;
            }
        }
        System::setVar('idshtmlfields', $idshtmlarray);

        $idsjsonfields = $request->request->get('idsjsonfields', '');
        $idsjsonfields = explode(PHP_EOL, $idsjsonfields);
        $idsjsonarray = [];
        foreach ($idsjsonfields as $idsjsonfield) {
            $idsjsonfield = trim($idsjsonfield);
            if (!empty($idsjsonfield)) {
                $idsjsonarray[] = $idsjsonfield;
            }
        }
        System::setVar('idsjsonfields', $idsjsonarray);

        $idsexceptions = $request->request->get('idsexceptions', '');
        $idsexceptions = explode(PHP_EOL, $idsexceptions);
        $idsexceptarray = [];
        foreach ($idsexceptions as $idsexception) {
            $idsexception = trim($idsexception);
            if (!empty($idsexception)) {
                $idsexceptarray[] = $idsexception;
            }
        }
        System::setVar('idsexceptions', $idsexceptarray);

        // clear all cache and compile directories
        ModUtil::apiFunc('ZikulaSettingsModule', 'admin', 'clearallcompiledcaches');

        // the module configuration has been updated successfuly
        if ($validates) {
            $request->getSession()->getFlashBag()->add('status', $this->__('Done! Saved module configuration.'));
        }

        // we need to auto logout the user if they changed from DB to FILE
        if ($cause_logout == true) {
            UserUtil::logout();
            $request->getSession()->getFlashBag()->add('status', $this->__('Session handling variables have changed. You must log in again.'));
            $returnPage = urlencode($this->get('router')->generate('zikulasecuritycentermodule_admin_modifyconfig', [], RouterInterface::ABSOLUTE_URL));

            return new RedirectResponse($this->get('router')->generate('zikulausersmodule_user_login', ['returnpage' => $returnPage], RouterInterface::ABSOLUTE_URL));
        }

        // the user to an appropriate page for them to carry on their work
        return new RedirectResponse($this->get('router')->generate('zikulasecuritycentermodule_admin_modifyconfig', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/purifierconfig/{reset}")
     * @Method("GET")
     *
     * HTMLPurifier configuration.
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function purifierconfigAction(Request $request, $reset = null)
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaSecurityCenterModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $this->view->assign('itemsperpage', $this->getVar('itemsperpage'));

        if (isset($reset) && ($reset == 'default')) {
            $purifierconfig = SecurityCenterUtil::getPurifierConfig(true);
            $request->getSession()->getFlashBag()->add('status', $this->__('Default values for HTML Purifier were successfully loaded. Please store them using the "Save" button at the bottom of this page'));
        } else {
            $purifierconfig = SecurityCenterUtil::getPurifierConfig(false);
        }

        $purifier = new \HTMLPurifier($purifierconfig);

        $config = $purifier->config;

        if (is_array($config) && isset($config[0])) {
            $config = $config[1];
        }

        $allowed = \HTMLPurifier_Config::getAllowedDirectivesForForm(true, $config->def);

        // list of excluded directives, format is $namespace_$directive
        $excluded = ['Cache_SerializerPath'];

        $purifierAllowed = [];
        foreach ($allowed as $allowedDirective) {
            list($namespace, $directive) = $allowedDirective;

            if (in_array($namespace . '_' . $directive, $excluded)) {
                continue;
            }

            if ($namespace == 'Filter') {
                if (
                // Do not allow Filter.Custom for now. Causing errors.
                // TODO research why Filter.Custom is causing exceptions and correct.
                        ($directive == 'Custom')
                        // Do not allow Filter.ExtractStyleBlock* for now. Causing errors.
                        // TODO Filter.ExtractStyleBlock* requires CSSTidy
                        || (stripos($directive, 'ExtractStyleBlock') !== false)
                ) {
                    continue;
                }
            }

            $directiveRec = [];
            $directiveRec['key'] = $namespace . '.' . $directive;
            $def = $config->def->info[$directiveRec['key']];
            $directiveRec['value'] = $config->get($directiveRec['key']);
            if (is_int($def)) {
                $directiveRec['allowNull'] = ($def < 0);
                $directiveRec['type'] = abs($def);
            } else {
                $directiveRec['allowNull'] = (isset($def->allow_null) && $def->allow_null);
                $directiveRec['type'] = (isset($def->type) ? $def->type : 0);
                if (isset($def->allowed)) {
                    $directiveRec['allowedValues'] = [];
                    foreach ($def->allowed as $val => $b) {
                        $directiveRec['allowedValues'][] = $val;
                    }
                }
            }
            if (is_array($directiveRec['value'])) {
                switch ($directiveRec['type']) {
                    case \HTMLPurifier_VarParser::LOOKUP:
                        $value = [];
                        foreach ($directiveRec['value'] as $val => $b) {
                            $value[] = $val;
                        }
                        $directiveRec['value'] = implode(PHP_EOL, $value);
                        break;
                    case \HTMLPurifier_VarParser::ALIST:
                        $directiveRec['value'] = implode(PHP_EOL, $directiveRec['value']);
                        break;
                    case \HTMLPurifier_VarParser::HASH:
//                        $value = '';
//                        foreach ($directiveRec['value'] as $i => $v) {
//                            $value .= "{$i}:{$v}" . PHP_EOL;
//                        }
                        $directiveRec['value'] = json_encode($directiveRec['value']);
                        break;
                    default:
                        $directiveRec['value'] = '';
                }
            }
            // Editing for only these types is supported
            $directiveRec['supported'] = (($directiveRec['type'] == \HTMLPurifier_VarParser::STRING)
                    || ($directiveRec['type'] == \HTMLPurifier_VarParser::ISTRING)
                    || ($directiveRec['type'] == \HTMLPurifier_VarParser::TEXT)
                    || ($directiveRec['type'] == \HTMLPurifier_VarParser::ITEXT)
                    || ($directiveRec['type'] == \HTMLPurifier_VarParser::INT)
                    || ($directiveRec['type'] == \HTMLPurifier_VarParser::FLOAT)
                    || ($directiveRec['type'] == \HTMLPurifier_VarParser::BOOL)
                    || ($directiveRec['type'] == \HTMLPurifier_VarParser::LOOKUP)
                    || ($directiveRec['type'] == \HTMLPurifier_VarParser::ALIST)
                    || ($directiveRec['type'] == \HTMLPurifier_VarParser::HASH));

            $purifierAllowed[$namespace][$directive] = $directiveRec;
        }

        $this->view->assign('purifier', $purifier)
                ->assign('purifierTypes', \HTMLPurifier_VarParser::$types)
                ->assign('purifierAllowed', $purifierAllowed);

        return new Response($this->view->fetch('Admin/purifierconfig.tpl'));
    }

    /**
     * @Route("/purifierconfig")
     * @Method("POST")
     *
     * Update HTMLPurifier configuration.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function updatepurifierconfigAction(Request $request)
    {
        $this->checkCsrfToken();

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaSecurityCenterModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Load HTMLPurifier Classes
        $purifier = SecurityCenterUtil::getpurifier();

        // Update module variables.
        $config = $request->request->get('purifierConfig', null);
        $config = \HTMLPurifier_Config::prepareArrayFromForm($config, false, true, true, $purifier->config->def);

        $allowed = \HTMLPurifier_Config::getAllowedDirectivesForForm(true, $purifier->config->def);
        foreach ($allowed as $allowedDirective) {
            list($namespace, $directive) = $allowedDirective;

            $directiveKey = $namespace . '.' . $directive;
            $def = $purifier->config->def->info[$directiveKey];

            if (isset($config[$namespace])
                    && array_key_exists($directive, $config[$namespace])
                    && is_null($config[$namespace][$directive])) {
                unset($config[$namespace][$directive]);

                if (count($config[$namespace]) <= 0) {
                    unset($config[$namespace]);
                }
            }

            if (isset($config[$namespace]) && isset($config[$namespace][$directive])) {
                if (is_int($def)) {
                    $directiveType = abs($def);
                } else {
                    $directiveType = (isset($def->type) ? $def->type : 0);
                }

                switch ($directiveType) {
                    case \HTMLPurifier_VarParser::LOOKUP:
                        $value = explode(PHP_EOL, $config[$namespace][$directive]);
                        $config[$namespace][$directive] = [];
                        foreach ($value as $val) {
                            $val = trim($val);
                            if (!empty($val)) {
                                $config[$namespace][$directive][$val] = true;
                            }
                        }
                        if (empty($config[$namespace][$directive])) {
                            unset($config[$namespace][$directive]);
                        }
                        break;
                    case \HTMLPurifier_VarParser::ALIST:
                        $value = explode(PHP_EOL, $config[$namespace][$directive]);
                        $config[$namespace][$directive] = [];
                        foreach ($value as $val) {
                            $val = trim($val);
                            if (!empty($val)) {
                                $config[$namespace][$directive][] = $val;
                            }
                        }
                        if (empty($config[$namespace][$directive])) {
                            unset($config[$namespace][$directive]);
                        }
                        break;
                    case \HTMLPurifier_VarParser::HASH:
                        $value = explode(PHP_EOL, $config[$namespace][$directive]);
                        $config[$namespace][$directive] = [];
                        foreach ($value as $val) {
                            list($i, $v) = explode(':', $val);
                            $i = trim($i);
                            $v = trim($v);
                            if (!empty($i) && !empty($v)) {
                                $config[$namespace][$directive][$i] = $v;
                            }
                        }
                        if (empty($config[$namespace][$directive])) {
                            unset($config[$namespace][$directive]);
                        }
                        break;
                }
            }

            if (isset($config[$namespace])
                    && array_key_exists($directive, $config[$namespace])
                    && is_null($config[$namespace][$directive])) {
                unset($config[$namespace][$directive]);

                if (count($config[$namespace]) <= 0) {
                    unset($config[$namespace]);
                }
            }
        }

        $this->setVar('htmlpurifierConfig', serialize($config));

        // clear all cache and compile directories
        ModUtil::apiFunc('ZikulaSettingsModule', 'admin', 'clearallcompiledcaches');

        // the module configuration has been updated successfuly
        $request->getSession()->getFlashBag()->add('status', $this->__('Done! Saved HTMLPurifier configuration.'));

        return new RedirectResponse($this->get('router')->generate('zikulasecuritycentermodule_admin_modifyconfig', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/idslog")
     *
     * Function to view ids log events.
     *
     * @param Request $request
     *
     * @return Response symfony response object.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function viewidslogAction(Request $request)
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaSecurityCenterModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // sorting
        $sort = $request->get('sort', 'date DESC');
        $sort_exp = explode(' ', $sort);
        $sorting = [$sort_exp[0] => (isset($sort_exp[1]) ? $sort_exp[1] : 'ASC')];

        // filtering
        $filterdefault = [
            'uid' => 0,
            'name' => null,
            'tag' => null,
            'value' => null,
            'page' => null,
            'ip' => null,
            'impact' => null
        ];
        $filter = $request->get('filter', $filterdefault);
        $where = [];
        foreach ($filter as $flt_key => $flt_value) {
            if (isset($flt_value) && !empty($flt_value)) {
                $where[$flt_key] = $flt_value;
            }
        }

        // offset
        $startnum = (int)$request->get('startnum', 0);

        // number of items to show
        $pagesize = (int)$this->getVar('pagesize', 25);

        // get data
        $item_params = [
            'where' => $where,
            'sorting' => $sorting,
            'limit' => $pagesize,
            'offset' => $startnum
        ];
        $items = ModUtil::apiFunc('ZikulaSecurityCenterModule', 'admin', 'getAllIntrusions', $item_params);

        $data = [];
        foreach ($items as $item) {
            $dta = $item->toArray();
            $dta['username'] = $dta['user']['uname'];
            $dta['filters'] = unserialize($dta['filters']);
            unset($dta['user']);
            $data[] = $dta;
        }

        // Create output object
        $this->view->assign('filter', $filter)
                   ->assign('sort', $sort)
                   ->assign('objectArray', $data);

        // Assign the values for the smarty plugin to produce a pager.
        $pager = [];
        $pager['numitems'] = ModUtil::apiFunc('ZikulaSecurityCenterModule', 'admin', 'countAllIntrusions', $item_params);
        $pager['itemsperpage'] = $pagesize;

        $this->view->assign('startnum', $startnum)
                   ->assign('pager', $pager);

        $csrftoken = SecurityUtil::generateCsrfToken($this->getContainer(), true);
        $this->view->assign('csrftoken', $csrftoken);

        // fetch output from template
        return new Response($this->view->fetch('Admin/viewidslog.tpl'));
    }

    /**
     * @Route("/exportidslog")
     *
     * Export ids log.
     *
     * @param Request $request
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function exportidslogAction(Request $request)
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaSecurityCenterModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // get input values
        $confirmed = (int)$request->request->get('confirmed', (isset($args['confirmed']) ? $args['confirmed'] : 0));

        if ($confirmed == 1) {

            // export the titles ?
            $exportTitles = $request->request->get('exportTitles', (isset($args['exportTitles']) ? $args['exportTitles'] : null));
            $exportTitles = (!isset($exportTitles) || $exportTitles !== '1') ? false : true;

            // name of the exported file
            $exportFile = $request->request->get('exportFile', (isset($args['exportFile']) ? $args['exportFile'] : null));
            if (!isset($exportFile) || $exportFile == '') {
                $exportFile = 'idslog.csv';
            }
            if (!strrpos($exportFile, '.csv')) {
                $exportFile .= '.csv';
            }

            // delimeter
            $delimiter = $request->request->get('delimiter', (isset($args['delimiter']) ? $args['delimiter'] : null));
            if (!isset($delimiter) || $delimiter == '') {
                $delimiter = 1;
            }
            switch ($delimiter) {
                case 1:
                    $delimiter = ",";
                    break;
                case 2:
                    $delimiter = ";";
                    break;
                case 3:
                    $delimiter = ":";
                    break;
                case 4:
                    $delimiter = chr(9);
            }

            // titles
            if ($exportTitles == 1) {
                $titles = [
                    $this->__('Name'),
                    $this->__('Tag'),
                    $this->__('Value'),
                    $this->__('Page'),
                    $this->__('User Name'),
                    $this->__('IP'),
                    $this->__('Impact'),
                    $this->__('PHPIDS filters used'),
                    $this->__('Date')
                ];
            } else {
                $titles = [];
            }

            // get data
            $item_params = [
                'sorting' => ['date' => 'DESC']
            ];
            $items = ModUtil::apiFunc('ZikulaSecurityCenterModule', 'admin', 'getAllIntrusions', $item_params);

            $objData = [];
            foreach ($items as $item) {
                $dta = $item->toArray();
                $dta['username'] = $dta['user']['uname'];
                $dta['filters'] = unserialize($dta['filters']);
                $dta['date'] = $dta['date']->format('Y-m-d H:i:s');
                unset($dta['user']);
                $objData[] = $dta;
            }

            $data = [];
            $find = ["\r\n", "\n"];
            $replace = ['', ''];

            foreach ($objData as $key => $idsdata) {
                $filtersused = '';
                foreach ($objData[$key]['filters'] as $filter) {
                    $filtersused .= $filter['id'] . ' ';
                }

                $datarow = [
                    $objData[$key]['name'],
                    $objData[$key]['tag'],
                    htmlspecialchars(str_replace($find, $replace, $objData[$key]['value']), ENT_COMPAT, 'UTF-8', false),
                    htmlspecialchars($objData[$key]['page'], ENT_COMPAT, 'UTF-8', false),
                    $objData[$key]['username'],
                    $objData[$key]['ip'],
                    $objData[$key]['impact'],
                    $filtersused,
                    $objData[$key]['date']
                ];

                array_push($data, $datarow);
            }

            // export the csv file
            \FileUtil::exportCSV($data, $titles, $delimiter, '"', $exportFile);
        }

        // fetch output from template
        return new Response($this->view->fetch('Admin/exportidslog.tpl'));
    }

    /**
     * @Route("/purgeidslog")
     *
     * Purge ids log.
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function purgeidslogAction(Request $request)
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaSecurityCenterModule::', '::', ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        $confirmation = $request->get('confirmation');

        // Check for confirmation
        if (empty($confirmation)) {
            // No confirmation yet - get one

            return new Response($this->view->fetch('Admin/purgeidslog.tpl'));
        }
        // Confirm authorisation code
        $this->checkCsrfToken();

        // delete all entries
        if (ModUtil::apiFunc('ZikulaSecurityCenterModule', 'admin', 'purgeidslog')) {
            $request->getSession()->getFlashBag()->add('status', $this->__('Done! Purged IDS Log.'));
        }

        return new RedirectResponse($this->get('router')->generate('zikulasecuritycentermodule_admin_viewidslog', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/allowedhtml")
     * @Method("GET")
     *
     * Display the allowed html form.
     *
     * @return Response symfony response object.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function allowedhtmlAction()
    {
        // security check
        if (!SecurityUtil::checkPermission('ZikulaSecurityCenterModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $this->view->assign('htmltags', $this->_gethtmltags())
                   ->assign('currenthtmltags', System::getVar('AllowableHTML'))
                   ->assign('htmlentities', System::getVar('htmlentities'));

        // check for HTML Purifier outputfilter
        $htmlpurifier = (bool)(System::getVar('outputfilter') == 1);
        $this->view->assign('htmlpurifier', $htmlpurifier);

        $this->view->assign('configurl', $this->get('router')->generate('zikulasecuritycentermodule_admin_modifyconfig'));

        return new Response($this->view->fetch('Admin/allowedhtml.tpl'));
    }

    /**
     * @Route("/allowedhtml")
     * @Method("POST")
     *
     * Update allowed html settings.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function updateallowedhtmlAction(Request $request)
    {
        $this->checkCsrfToken();

        // security check
        if (!SecurityUtil::checkPermission('ZikulaSecurityCenterModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // update the allowed html settings
        $allowedhtml = [];
        $htmltags = $this->_gethtmltags();
        foreach ($htmltags as $htmltag => $usagetag) {
            $tagval = (int)$request->request->get('htmlallow' . $htmltag . 'tag', 0);
            if (($tagval != 1) && ($tagval != 2)) {
                $tagval = 0;
            }
            $allowedhtml[$htmltag] = $tagval;
        }

        System::setVar('AllowableHTML', $allowedhtml);

        // one additonal config var is set on this page
        $htmlentities = $request->request->get('xhtmlentities', 0);
        System::setVar('htmlentities', $htmlentities);

        // clear all cache and compile directories
        ModUtil::apiFunc('ZikulaSettingsModule', 'admin', 'clearallcompiledcaches');

        // all done successfully
        $request->getSession()->getFlashBag()->add('status', $this->__('Done! Saved module configuration.'));

        return new RedirectResponse($this->get('router')->generate('zikulasecuritycentermodule_admin_allowedhtml', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * Utility function to return the list of available tags.
     *
     * @return array
     */
    private function _gethtmltags()
    {
        // Possible allowed HTML tags
        return [
            '!--' => 'http://www.w3schools.com/html5/tag_comment.asp',
            'a' => 'http://www.w3schools.com/html5/tag_a.asp',
            'abbr' => 'http://www.w3schools.com/html5/tag_abbr.asp',
            'acronym' => 'http://www.w3schools.com/html5/tag_acronym.asp',
            'address' => 'http://www.w3schools.com/html5/tag_address.asp',
            'applet' => 'http://www.w3schools.com/tags/tag_applet.asp',
            'area' => 'http://www.w3schools.com/html5/tag_area.asp',
            'article' => 'http://www.w3schools.com/html5/tag_article.asp',
            'aside' => 'http://www.w3schools.com/html5/tag_aside.asp',
            'audio' => 'http://www.w3schools.com/html5/tag_audio.asp',
            'b' => 'http://www.w3schools.com/html5/tag_b.asp',
            'base' => 'http://www.w3schools.com/html5/tag_base.asp',
            'basefont' => 'http://www.w3schools.com/tags/tag_basefont.asp',
            'bdo' => 'http://www.w3schools.com/html5/tag_bdo.asp',
            'big' => 'http://www.w3schools.com/tags/tag_font_style.asp',
            'blockquote' => 'http://www.w3schools.com/html5/tag_blockquote.asp',
            'br' => 'http://www.w3schools.com/html5/tag_br.asp',
            'button' => 'http://www.w3schools.com/html5/tag_button.asp',
            'canvas' => 'http://www.w3schools.com/html5/tag_canvas.asp',
            'caption' => 'http://www.w3schools.com/html5/tag_caption.asp',
            'center' => 'http://www.w3schools.com/tags/tag_center.asp',
            'cite' => 'http://www.w3schools.com/html5/tag_phrase_elements.asp',
            'code' => 'http://www.w3schools.com/html5/tag_phrase_elements.asp',
            'col' => 'http://www.w3schools.com/html5/tag_col.asp',
            'colgroup' => 'http://www.w3schools.com/html5/tag_colgroup.asp',
            'command' => 'http://www.w3schools.com/html5/tag_command.asp',
            'datalist' => 'http://www.w3schools.com/html5/tag_datalist.asp',
            'dd' => 'http://www.w3schools.com/html5/tag_dd.asp',
            'del' => 'http://www.w3schools.com/html5/tag_del.asp',
            'details' => 'http://www.w3schools.com/html5/tag_details.asp',
            'dfn' => 'http://www.w3schools.com/html5/tag_phrase_elements.asp',
            'dir' => 'http://www.w3schools.com/tags/tag_dir.asp',
            'div' => 'http://www.w3schools.com/html5/tag_div.asp',
            'dl' => 'http://www.w3schools.com/html5/tag_dl.asp',
            'dt' => 'http://www.w3schools.com/html5/tag_dt.asp',
            'em' => 'http://www.w3schools.com/html5/tag_phrase_elements.asp',
            'embed' => 'http://www.w3schools.com/html5/tag_embed.asp',
            'fieldset' => 'http://www.w3schools.com/html5/tag_fieldset.asp',
            'figcaption' => 'http://www.w3schools.com/html5/tag_figcaption.asp',
            'figure' => 'http://www.w3schools.com/html5/tag_figure.asp',
            'font' => 'http://www.w3schools.com/tags/tag_font.asp',
            'footer' => 'http://www.w3schools.com/html5/tag_footer.asp',
            'form' => 'http://www.w3schools.com/html5/tag_form.asp',
            'h1' => 'http://www.w3schools.com/html5/tag_hn.asp',
            'h2' => 'http://www.w3schools.com/html5/tag_hn.asp',
            'h3' => 'http://www.w3schools.com/html5/tag_hn.asp',
            'h4' => 'http://www.w3schools.com/html5/tag_hn.asp',
            'h5' => 'http://www.w3schools.com/html5/tag_hn.asp',
            'h6' => 'http://www.w3schools.com/html5/tag_hn.asp',
            'header' => 'http://www.w3schools.com/html5/tag_header.asp',
            'hgroup' => 'http://www.w3schools.com/html5/tag_hgroup.asp',
            'hr' => 'http://www.w3schools.com/html5/tag_hr.asp',
            'i' => 'http://www.w3schools.com/html5/tag_i.asp',
            'iframe' => 'http://www.w3schools.com/html5/tag_iframe.asp',
            'img' => 'http://www.w3schools.com/html5/tag_img.asp',
            'input' => 'http://www.w3schools.com/html5/tag_input.asp',
            'ins' => 'http://www.w3schools.com/html5/tag_ins.asp',
            'keygen' => 'http://www.w3schools.com/html5/tag_keygen.asp',
            'kbd' => 'http://www.w3schools.com/html5/tag_phrase_elements.asp',
            'label' => 'http://www.w3schools.com/html5/tag_label.asp',
            'legend' => 'http://www.w3schools.com/html5/tag_legend.asp',
            'li' => 'http://www.w3schools.com/html5/tag_li.asp',
            'map' => 'http://www.w3schools.com/html5/tag_map.asp',
            'mark' => 'http://www.w3schools.com/html5/tag_mark.asp',
            'menu' => 'http://www.w3schools.com/html5/tag_menu.asp',
            'marquee' => '',
            'meter' => 'http://www.w3schools.com/html5/tag_meter.asp',
            'nav' => 'http://www.w3schools.com/html5/tag_nav.asp',
            'nobr' => '',
            'object' => 'http://www.w3schools.com/html5/tag_object.asp',
            'ol' => 'http://www.w3schools.com/html5/tag_ol.asp',
            'optgroup' => 'http://www.w3schools.com/html5/tag_optgroup.asp',
            'option' => 'http://www.w3schools.com/html5/tag_option.asp',
            'output' => 'http://www.w3schools.com/html5/tag_output.asp',
            'p' => 'http://www.w3schools.com/html5/tag_p.asp',
            'param' => 'http://www.w3schools.com/html5/tag_param.asp',
            'pre' => 'http://www.w3schools.com/html5/tag_pre.asp',
            'progress' => 'http://www.w3schools.com/html5/tag_progress.asp',
            'q' => 'http://www.w3schools.com/html5/tag_q.asp',
            'rp' => 'http://www.w3schools.com/html5/tag_rp.asp',
            'rt' => 'http://www.w3schools.com/html5/tag_rt.asp',
            'ruby' => 'http://www.w3schools.com/html5/tag_ruby.asp',
            's' => 'http://www.w3schools.com/tags/tag_strike.asp',
            'samp' => 'http://www.w3schools.com/html5/tag_phrase_elements.asp',
            'script' => 'http://www.w3schools.com/html5/tag_script.asp',
            'section' => 'http://www.w3schools.com/html5/tag_section.asp',
            'select' => 'http://www.w3schools.com/html5/tag_select.asp',
            'small' => 'http://www.w3schools.com/html5/tag_small.asp',
            'source' => 'http://www.w3schools.com/html5/tag_source.asp',
            'span' => 'http://www.w3schools.com/html5/tag_span.asp',
            'strike' => 'http://www.w3schools.com/tags/tag_strike.asp',
            'strong' => 'http://www.w3schools.com/html5/tag_phrase_elements.asp',
            'sub' => 'http://www.w3schools.com/html5/tag_sup.asp',
            'summary' => 'http://www.w3schools.com/html5/tag_summary.asp',
            'sup' => 'http://www.w3schools.com/html5/tag_sup.asp',
            'table' => 'http://www.w3schools.com/html5/tag_table.asp',
            'tbody' => 'http://www.w3schools.com/html5/tag_tbody.asp',
            'td' => 'http://www.w3schools.com/html5/tag_td.asp',
            'textarea' => 'http://www.w3schools.com/html5/tag_textarea.asp',
            'tfoot' => 'http://www.w3schools.com/html5/tag_tfoot.asp',
            'th' => 'http://www.w3schools.com/html5/tag_th.asp',
            'thead' => 'http://www.w3schools.com/html5/tag_thead.asp',
            'time' => 'http://www.w3schools.com/html5/tag_time.asp',
            'tr' => 'http://www.w3schools.com/html5/tag_tr.asp',
            'tt' => 'http://www.w3schools.com/tags/tag_font_style.asp',
            'u' => 'http://www.w3schools.com/tags/tag_u.asp',
            'ul' => 'http://www.w3schools.com/html5/tag_ul.asp',
            'var' => 'http://www.w3schools.com/html5/tag_phrase_elements.asp',
            'video' => 'http://www.w3schools.com/html5/tag_video.asp',
            'wbr' => 'http://www.w3schools.com/html5/tag_wbr.asp'
        ];
    }
}
