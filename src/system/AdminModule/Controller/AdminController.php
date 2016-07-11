<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Controller;

use ModUtil;
use System;
use Zikula_Core;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * NOTE: intentionally no class level route setting here
 *
 * Administrative controllers for the admin module
 */
class AdminController extends AbstractController
{
    /**
     * @Route("")
     *
     * the main administration function
     *
     * This function is the default function, and is called whenever the
     * module is initiated without defining arguments.  As such it can
     * be used for a number of things, but most commonly it either just
     * shows the module menu and returns or calls whatever the module
     * designer feels should be the default function (often this is the
     * view() function)
     *
     * @return RedirectResponse symfony response object
     */
    public function indexAction()
    {
        // Security check will be done in view()
        return $this->redirectToRoute('zikulaadminmodule_admin_view');
    }

    /**
     * @Route("/categories/{startnum}", requirements={"startnum" = "\d+"})
     * @Method("GET")
     * @Theme("admin")
     * @Template
     *
     * View all admin categories
     *
     * @param integer $startnum
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permission to the module
     */
    public function viewAction($startnum = 0)
    {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $itemsPerPage = $this->getVar('itemsperpage');

        $categories = [];
        $items = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getall', [
            'startnum' => $startnum,
            'numitems' => $itemsPerPage
        ]);
        foreach ($items as $item) {
            if ($this->hasPermission('ZikulaAdminModule::', $item['name'] . '::' . $item['cid'], ACCESS_READ)) {
                $categories[] = $item;
            }
        }

        $amountOfItems = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'countitems');

        return [
            'categories' => $categories,
            'pager' => [
                'amountOfItems' => $amountOfItems,
                'itemsPerPage' => $itemsPerPage
            ]
        ];
    }

    /**
     * @Route("/newcategory")
     * @Method("GET")
     * @Theme("admin")
     * @Template
     *
     * Display a new admin category form
     *
     * Displays a form for the user to input the details of the new category. Data is supplied to @see this::createAction()
     *
     * @param Request $request
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to add a category
     */
    public function newcatAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaAdminModule::Item', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm('Zikula\AdminModule\Form\Type\CreateCategoryType', [], [
            'translator' => $this->get('translator.default')
        ]);

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();

                // Security check
                if (!$this->hasPermission('ZikulaAdminModule::Category', $formData['name'] . '::', ACCESS_ADD)) {
                    throw new AccessDeniedException();
                }

                $cid = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'create', $formData);
                if (is_numeric($cid)) {
                    $this->addFlash('status', $this->__('Done! Created new category.'));
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }
            if ($form->get('help')->isClicked()) {
                return $this->redirect($this->generateUrl('zikulaadminmodule_admin_help') . '#new');
            }

            return $this->redirectToRoute('zikulaadminmodule_admin_view');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/modifycategory/{cid}", requirements={"cid" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template
     *
     * Displays a modify category form
     *
     * Displays a form for the user to edit the details of a category. Data is supplied to @see this::updateAction()
     *
     * @param Request $request
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to edit the category
     * @throws NotFoundHttpException Thrown if the requested category cannot be found
     */
    public function modifyAction(Request $request, $cid)
    {
        $category = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getCategory', ['cid' => $cid]);
        if (empty($category)) {
            throw new NotFoundHttpException($this->__('Error! No such category found.'));
        }

        if (!$this->hasPermission('ZikulaAdminModule::Category', $category['name'] . '::' . $cid, ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm('Zikula\AdminModule\Form\Type\EditCategoryType', $category, [
            'translator' => $this->get('translator.default')
        ]);

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();

                if (!$this->hasPermission('ZikulaAdminModule::Category', $formData['name'] . '::' . $formData['cid'], ACCESS_EDIT)) {
                    throw new AccessDeniedException();
                }

                $update = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'update', $formData);
                if ($update) {
                    $this->addFlash('status', $this->__('Done! Saved category.'));
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }
            if ($form->get('help')->isClicked()) {
                return $this->redirect($this->generateUrl('zikulaadminmodule_admin_help') . '#modify');
            }

            return $this->redirectToRoute('zikulaadminmodule_admin_view');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/deletecategory")
     * @Theme("admin")
     * @Template
     *
     * delete item
     *
     * This is a standard function that is called whenever an administrator
     * wishes to delete a current module item.
     *
     * @return Response Symfony response object if confirmation is null
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to delete the category
     * @throws NotFoundHttpException Thrown if the category cannot be found
     */
    public function deleteAction(Request $request)
    {
        // check where to get the parameters from for this dual purpose controller
        $cid = null;
        if ($request->isMethod('GET')) {
            $cid = $request->query->getDigits('cid', null);
        } elseif ($request->isMethod('POST')) {
            $cid = $request->request->getDigits('cid', null);
        }

        $category = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getCategory', ['cid' => $cid]);
        if (empty($category)) {
            throw new NotFoundHttpException($this->__('Error! No such category found.'));
        }

        if (!$this->hasPermission('ZikulaAdminModule::Category', "$category[name]::$cid", ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm('Zikula\AdminModule\Form\Type\DeleteCategoryType', $category, [
            'translator' => $this->get('translator.default')
        ]);

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $formData = $form->getData();

                try {
                    // delete category
                    $delete = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'delete', ['cid' => $formData['cid']]);
                    if ($delete) {
                        $this->addFlash('status', $this->__('Done! Category deleted.'));
                    }
                } catch (\RuntimeException $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulaadminmodule_admin_view');
        }

        return [
            'form' => $form->createView(),
            'category' => $category
        ];
    }

    /**
     * @Route("/panel/{acid}", requirements={"acid" = "^[1-9]\d*$"})
     * @Method("GET")
     * @Theme("admin")
     * @Template
     *
     * Display main admin panel for a category
     *
     * @param integer $acid
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions to the module
     */
    public function adminpanelAction($acid = null)
    {
        if (!$this->hasPermission('::', '::', ACCESS_EDIT)) {
            // suppress admin display - return to index.
            if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_EDIT)) {
                throw new AccessDeniedException();
            }
        }

        if (!$this->getVar('ignoreinstallercheck') && $this->get('kernel')->getEnvironment() == 'dev') {
            // check if the Zikula Recovery Console exists
            $zrcExists = file_exists('zrc.php');
            // check if upgrade scripts exist
            if (true == $zrcExists) {
                return $this->render('@ZikulaAdminModule/Admin/warning.html.twig', [
                    'zrcExists' => $zrcExists
                ]);
            }
        }

        // Now prepare the display of the admin panel by getting the relevant info.

        // cid isn't set, so go to the default category
        if (empty($acid)) {
            $acid = $this->getVar('startcategory');
        }

        $templateParameters = [
            // Add category menu to output
            'menu' => $this->categorymenuAction($acid)->getContent()
        ];

        // Check to see if we have access to the requested category.
        if (!$this->hasPermission('ZikulaAdminModule::', "::$acid", ACCESS_ADMIN)) {
            $acid = -1;
        }

        // Get details for selected category
        $category = null;
        if ($acid > 0) {
            $category = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getCategory', ['cid' => $acid]);
        }

        if (!$category) {
            // get the default category
            $acid = $this->getVar('startcategory');

            // Check to see if we have access to the requested category.
            if (!$this->hasPermission('ZikulaAdminModule::', "::$acid", ACCESS_ADMIN)) {
                throw new AccessDeniedException();
            }

            $category = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getCategory', ['cid' => $acid]);
        }

        // assign the category
        $templateParameters['category'] = $category;

        $displayNameType = $this->getVar('displaynametype', 1);

        // get admin capable modules
        $adminModules = ModUtil::getModulesCapableOf('admin');
        $adminLinks = [];
        $baseUrl = System::getBaseUrl();
        foreach ($adminModules as $adminModule) {
            if (!$this->hasPermission($adminModule['name'] . '::', 'ANY', ACCESS_EDIT)) {
                continue;
            }

            $catid = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getmodcategory',
                    ['mid' => ModUtil::getIdFromName($adminModule['name'])]);
            $order = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getSortOrder',
                    ['mid' => ModUtil::getIdFromName($adminModule['name'])]);

            if ($catid == $acid || (false == $catid && $acid == $this->getVar('defaultcategory'))) {
                $menuTextUrl = isset($adminModule['capabilities']['admin']['url'])
                    ? $adminModule['capabilities']['admin']['url']
                    : $this->get('router')->generate($adminModule['capabilities']['admin']['route']);

                $menuText = '';
                if ($displayNameType == 1) {
                    $menuText = $adminModule['displayname'];
                } elseif ($displayNameType == 2) {
                    $menuText = $adminModule['name'];
                } elseif ($displayNameType == 3) {
                    $menuText = $adminModule['displayname'] . ' (' . $adminModule['name'] . ')';
                }

                $linkCollection = $this->get('zikula.link_container_collector')->getLinks($adminModule['name'], 'admin');
                $links = (false == $linkCollection)
                    ? (array) ModUtil::apiFunc($adminModule['name'], 'admin', 'getLinks')
                    : $linkCollection
                    ;

                $adminLinks[] = [
                    'menuTextUrl' => $menuTextUrl,
                    'menuText' => $menuText,
                    'menuTextTitle' => $adminModule['description'],
                    'moduleName' => $adminModule['name'],
                    'adminIcon' => $baseUrl . ModUtil::getModuleImagePath($adminModule['name']),
                    'id' => $adminModule['id'],
                    'order' => $order,
                    'links' => $links
                ];
            }
        }
        usort($adminLinks, 'Zikula\AdminModule\Controller\AdminController::_sortAdminModsByOrder');
        $templateParameters['adminLinks'] = $adminLinks;

        return $templateParameters;
    }

    /**
     * @Route("/categorymenu/{acid}", requirements={"acid" = "^[1-9]\d*$"})
     * @Method("GET")
     * @Theme("admin")
     *
     * Main category menu.
     *
     * @param integer $acid
     *
     * @return Response symfony response object
     */
    public function categorymenuAction($acid = null)
    {
        $acid = empty($acid) ? $this->getVar('startcategory') : $acid;

        // Get all categories
        $categories = [];
        $items = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getall');
        foreach ($items as $item) {
            if ($this->hasPermission('ZikulaAdminModule::', "$item[name]::$item[cid]", ACCESS_READ)) {
                $categories[] = $item;
            }
        }

        // get admin capable modules
        $adminModules = ModUtil::getModulesCapableOf('admin');
        $adminLinks = [];

        foreach ($adminModules as $adminModule) {
            if (!$this->hasPermission($adminModule['name'] . '::', '::', ACCESS_EDIT)) {
                continue;
            }

            $catid = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getmodcategory', ['mid' => $adminModule['id']]);
            $order = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getSortOrder',
                                        ['mid' => ModUtil::getIdFromName($adminModule['name'])]);
            $menuTextUrl = isset($adminModule['capabilities']['admin']['url'])
                ? $adminModule['capabilities']['admin']['url']
                : $this->get('router')->generate($adminModule['capabilities']['admin']['route']);

            $adminLinks[$catid][] = [
                'menuTextUrl' => $menuTextUrl,
                'menuText' => $adminModule['displayname'],
                'menuTextTitle' => $adminModule['description'],
                'moduleName' => $adminModule['name'],
                'order' => $order,
                'id' => $adminModule['id'],
                'icon' => ModUtil::getModuleImagePath($adminModule['name'])
            ];
        }

        foreach ($adminLinks as &$item) {
            usort($item, 'Zikula\AdminModule\Controller\AdminController::_sortAdminModsByOrder');
        }

        $menuOptions = [];
        $possibleCategoryIds = [];
        $permission = false;

        if (isset($categories) && is_array($categories)) {
            foreach ($categories as $category) {
                // only categories containing modules where the current user has permissions will
                // be shown, all others will be hidden
                // admin will see all categories
                if ((isset($adminLinks[$category['cid']]) && count($adminLinks[$category['cid']]))
                        || $this->hasPermission('.*', '.*', ACCESS_ADMIN)) {
                    $menuOption = [
                        'url' => $this->get('router')->generate('zikulaadminmodule_admin_adminpanel', ['acid' => $category['cid']]),
                        'title' => $category['name'],
                        'description' => $category['description'],
                        'cid' => $category['cid'],
                        'items' => isset($adminLinks[$category['cid']]) ? $adminLinks[$category['cid']] : []
                    ];

                    $menuOptions[$category['cid']] = $menuOption;
                    $possibleCategoryIds[] = $category['cid'];

                    if ($acid == $category['cid']) {
                        $permission = true;
                    }
                }
            }
        }

        // if permission is false we are not allowed to see this category because its
        // empty and we are not admin
        if (false == $permission) {
            // show the first category
            $acid = !empty($possibleCategoryIds) ? (int)$possibleCategoryIds[0] : null;
        }

        return $this->render('@ZikulaAdminModule/Admin/categoryMenu.html.twig', [
            'currentCategory' => $acid,
            'menuOptions' => $menuOptions
        ]);
    }

    /**
     * @Route("/header")
     *
     * Open the admin container
     *
     * @return Response symfony response object
     */
    public function adminheaderAction()
    {
        return $this->render('@ZikulaAdminModule/Admin/header.html.twig');
    }

    /**
     * @Route("/footer")
     *
     * Close the admin container
     *
     * @return Response symfony response object
     */
    public function adminfooterAction()
    {
        return $this->render('@ZikulaAdminModule/Admin/footer.html.twig', [
            'symfonyversion' => Kernel::VERSION
        ]);
    }

    /**
     * @Route("/help")
     * @Theme("admin")
     * @Template
     *
     * display the module help page
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission to the module
     */
    public function helpAction()
    {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        return [];
    }

    /**
     * Zikula curl
     *
     * This function is internal for the time being and may be extended to be a proper library
     * or find an alternative solution later.
     *
     * @param  string $url
     * @param  int    $timeout default=5
     *
     * @return string|bool false if no url handling functions are present or url string
     */
    private function _zcurl($url, $timeout = 5)
    {
        $urlArray = parse_url($url);
        $data = '';
        $userAgent = 'Zikula/' . Zikula_Core::VERSION_NUM;
        $ref = System::getBaseUrl();
        $port = ($urlArray['scheme'] == 'https') ? 443 : 80;
        if (ini_get('allow_url_fopen')) {
            // handle SSL connections
            $path_query = (isset($urlArray['query']) ? $urlArray['path'] . $urlArray['query'] : $urlArray['path']);
            $host = ($port == 443 ? "ssl://$urlArray[host]" : $urlArray['host']);
            $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
            if (!$fp) {
                return false;
            }

            $out = "GET $path_query? HTTP/1.1\r\n";
            $out .= "User-Agent: $userAgent\r\n";
            $out .= "Referer: $ref\r\n";
            $out .= "Host: $urlArray[host]\r\n";
            $out .= "Connection: Close\r\n\r\n";
            fwrite($fp, $out);
            while (!feof($fp)) {
                $data .= fgets($fp, 1024);
            }
            fclose($fp);
            $dataArray = explode("\r\n\r\n", $data);

            return $dataArray[1];
        } elseif (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_URL, "$url?");
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
            curl_setopt($ch, CURLOPT_REFERER, $ref);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            if (!ini_get('safe_mode') && !ini_get('open_basedir')) {
                // This option doesnt work in safe_mode or with open_basedir set in php.ini
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            }
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            $data = curl_exec($ch);
            if (!$data && $port = 443) {
                // retry non ssl
                $url = str_replace('https://', 'http://', $url);
                curl_setopt($ch, CURLOPT_URL, "$url?");
                $data = @curl_exec($ch);
            }
            //$headers = curl_getinfo($ch);
            curl_close($ch);

            return $data;
        } else {
            return false;
        }
    }

    /**
     * helper function to sort modules
     *
     * @param $a array first item to compare
     * @param $b array second item to compare
     *
     * @return int < 0 if module a should be ordered before module b > 0 otherwise
     */
    public static function _sortAdminModsByOrder($a, $b)
    {
        if ((int)$a['order'] == (int)$b['order']) {
            return strcmp($a['moduleName'], $b['moduleName']);
        }
        if ((int)$a['order'] > (int)$b['order']) {
            return 1;
        }
        if ((int)$a['order'] < (int)$b['order']) {
            return -1;
        }
    }
}
