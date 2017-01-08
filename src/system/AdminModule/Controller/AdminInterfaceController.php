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

use DataUtil;
use ModUtil;
use StringUtil;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;

/**
 * @Route("/admininterface")
 */
class AdminInterfaceController extends AbstractController
{
    /**
     * @Route("/header")
     *
     * Open the admin container
     *
     * @return Response symfony response object
     */
    public function headerAction()
    {
        return $this->render('@ZikulaAdminModule/AdminInterface/header.html.twig', [
            'caller' => $this->get('request_stack')->getMasterRequest()->attributes->all()
        ]);
    }

    /**
     * @Route("/footer")
     *
     * Close the admin container
     *
     * @return Response symfony response object
     */
    public function footerAction()
    {
        $caller = $this->get('request_stack')->getMasterRequest()->attributes->all();
        $caller['info'] = $this->get('zikula_extensions_module.extension_repository')->get($caller['_zkModule']);

        return $this->render('@ZikulaAdminModule/AdminInterface/footer.html.twig', [
            'caller' => $caller,
            'symfonyVersion' => Kernel::VERSION,
            'phpVersion' => phpversion()
        ]);
    }

    /**
     * @Route("/breadcrumbs")
     * @Method("GET")
     *
     * Admin breadcrumbs
     *
     * @return Response symfony response object
     */
    public function breadcrumbsAction()
    {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $masterRequest = $this->get('request_stack')->getMasterRequest();
        $caller = $masterRequest->attributes->all();
        $caller['info'] = $this->get('zikula_extensions_module.extension_repository')->get($caller['_zkModule']);

        $requestedCid = $masterRequest->attributes->get('acid');
        $defaultCid = empty($requestedCid) ? $this->getVar('startcategory') : $requestedCid;

        $cid = null;
        if ($caller['_zkModule'] == 'ZikulaAdminModule') {
            $cid = $defaultCid;
        } else {
            $moduleRelation = $this->get('doctrine')->getRepository('ZikulaAdminModule:AdminModuleEntity')->findOneBy(['mid' => $caller['info']['id']]);
            if (null !== $moduleRelation) {
                $cid = $moduleRelation->getCid();
            } else {
                $cid = $defaultCid;
            }
        }
        $caller['category'] = $this->get('doctrine')->getRepository('ZikulaAdminModule:AdminCategoryEntity')->find($cid);

        return $this->render('@ZikulaAdminModule/AdminInterface/breadCrumbs.html.twig', [
            'caller' => $caller
        ]);
    }

    /**
     * @Route("/developernotices")
     *
     * Add developer notices
     *
     * @return Response symfony response object
     */
    public function developernoticesAction()
    {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $modvars = $this->get('zikula_extensions_module.api.variable')->getAll('ZikulaThemeModule');
        $data = [];
        $data['mode'] = $this->get('kernel')->getEnvironment();
        if ($data['mode'] != 'prod') {
            $data['debug'] = $this->get('kernel')->isDebug() ? $this->__('Yes') : $this->__('No');
            $data['legacy'] = [
                'status' => true,
                'cssjscombine' => $modvars['cssjscombine'],
                'render' => [
                    'compile_check' => [
                        'state' => $modvars['render_compile_check'],
                        'title' => $this->__('Compile check')
                    ],
                    'force_compile' => [
                        'state' => $modvars['render_force_compile'],
                        'title' => $this->__('Force compile')
                    ],
                    'cache' => [
                        'state' => $modvars['render_cache'],
                        'title' => $this->__('Caching')
                    ]
                ],
                'theme' => [
                    'compile_check' => [
                        'state' => $modvars['compile_check'],
                        'title' => $this->__('Compile check')
                    ],
                    'force_compile' => [
                        'state' => $modvars['force_compile'],
                        'title' => $this->__('Force compile')
                    ],
                    'cache' => [
                        'state' => $modvars['enablecache'],
                        'title' => $this->__('Caching')
                    ]
                ]
            ];
        }

        return $this->render('@ZikulaAdminModule/AdminInterface/developerNotices.html.twig', [
            'developer' => $data
        ]);
    }

    /**
     * @Route("/securityanalyzer")
     *
     * Add security analyzer
     *
     * @param Request $request
     * @return Response symfony response object
     */
    public function securityanalyzerAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // check for .htaccess in app directory
        $app_htaccess = false;
        $appDir = $this->get('kernel')->getRootDir();
        if ($appDir) {
            // check if we have an absolute path which is possibly not within the document root
            $docRoot = $request->server->get('DOCUMENT_ROOT');
            if (StringUtil::left($appDir, 1) == '/' && false === strpos($appDir, $docRoot)) {
                // temp dir is outside the webroot, no .htaccess file needed
                $app_htaccess = true;
            } else {
                if (false === strpos($appDir, $docRoot)) {
                    $ldir = dirname(__FILE__);
                    $p = strpos($ldir, DIRECTORY_SEPARATOR . 'system'); // we are in system/AdminModule
                    $b = substr($ldir, 0, $p);
                    $filePath = $b.'/'.$appDir.'/.htaccess';
                } else {
                    $filePath = $appDir.'/.htaccess';
                }
                $app_htaccess = file_exists($filePath);
            }
        } else {
            // already customised, admin should know about what he's doing...
            $app_htaccess = true;
        }

        $variableApi = $this->get('zikula_extensions_module.api.variable');
        $hasSecurityCenter = $this->get('kernel')->isBundle('ZikulaSecurityCenterModule');

        return $this->render('@ZikulaAdminModule/AdminInterface/securityAnalyzer.html.twig', [
            'security' => [
                'config_php' => is_writable('config/config.php'),
                'magic_quotes_gpc' => DataUtil::getBooleanIniValue('magic_quotes_gpc'),
                'register_globals' => DataUtil::getBooleanIniValue('register_globals'),
                'app_htaccess' => $app_htaccess,
                'updatecheck' => $variableApi->getSystemVar('updatecheck'),
                'scactive' => $hasSecurityCenter,
                // check for outputfilter
                'useids' => $hasSecurityCenter && $variableApi->getSystemVar('useids') == 1,
                'idssoftblock' => $variableApi->getSystemVar('idssoftblock')
            ]
        ]);
    }

    /**
     * @Route("/updatecheck")
     *
     * Add update check
     *
     * @return Response symfony response object
     */
    public function updatecheckAction()
    {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $masterRequest = $this->get('request_stack')->getMasterRequest();

        return $this->render('@ZikulaAdminModule/AdminInterface/updateCheck.html.twig', [
            'caller' => [
                '_route' => $masterRequest->attributes->get('_route'),
                '_route_params' => $masterRequest->attributes->get('_route_params')
            ],
            'updateCheckHelper' => $this->get('zikula_admin_module.update_check_helper')
        ]);
    }

    /**
     * @Route("/menu")
     *
     * Admin menu.
     *
     * @return Response symfony response object
     */
    public function menuAction()
    {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $masterRequest = $this->get('request_stack')->getMasterRequest();
        $currentRequest = $this->get('request_stack')->getCurrentRequest();

        // get caller info
        $caller = [];
        $caller['_zkModule'] = $masterRequest->attributes->get('_zkModule');
        $caller['_zkType'] = $masterRequest->attributes->get('_zkType');
        $caller['_zkFunc'] = $masterRequest->attributes->get('_zkFunc');
        $caller['path'] = $masterRequest->getPathInfo();
        $caller['info'] = !empty($caller['_zkModule']) ? $this->get('zikula_extensions_module.extension_repository')->get($caller['_zkModule']) : [];

        // category we are in
        $requestedCid = $masterRequest->attributes->get('acid');
        $defaultCid = empty($requestedCid) ? $this->getVar('startcategory') : $requestedCid;
        if ($caller['_zkModule'] == 'ZikulaAdminModule' || empty($caller['_zkModule'])) {
            $cid = $defaultCid;
        } else {
            $moduleRelation = $this->get('doctrine')->getRepository('ZikulaAdminModule:AdminModuleEntity')->findOneBy(['mid' => $caller['info']['id']]);
            if (null !== $moduleRelation) {
                $cid = $moduleRelation->getCid();
            } else {
                $cid = $defaultCid;
            }
        }
        $caller['category'] = $this->get('doctrine')->getRepository('ZikulaAdminModule:AdminCategoryEntity')->find($cid);

        // mode requested
        $mode = $currentRequest->attributes->has('mode') ? $currentRequest->attributes->get('mode') : 'categories';
        $mode = in_array($mode, ['categories', 'modules']) ? $mode : 'categories';
        // template requested
        $template = $currentRequest->attributes->has('template') ? $currentRequest->attributes->get('template') : 'tabs';
        $template = in_array($template, ['tabs', 'panel']) ? $template : 'tabs';

        // get admin capable modules
        $adminModules = $this->get('zikula_extensions_module.api.capability')->getExtensionsCapableOf('admin');

        // sort modules by displayname
        $moduleNames = [];
        foreach ($adminModules as $key => $module) {
            $moduleNames[$key] = $module['displayname'];
        }
        array_multisort($moduleNames, SORT_ASC, $adminModules);
        $baseUrl = $masterRequest->getBasePath() . '/';

        $moduleCategories = $this->getDoctrine()->getManager()->getRepository('ZikulaAdminModule:AdminCategoryEntity')->getIndexedCollection('cid');
        $menuModules = [];
        $menuCategories = [];
        foreach ($adminModules as $adminModule) {
            if (!$this->hasPermission("$adminModule[name]::", '::', ACCESS_EDIT)) {
                continue;
            }

            $categoryAssignment = $this->get('doctrine')->getRepository('ZikulaAdminModule:AdminModuleEntity')->findOneBy(['mid' => $adminModule['id']]);
            if (null !== $categoryAssignment) {
                $catid = $categoryAssignment->getCid();
                $order = $categoryAssignment->getSortorder();
            } else {
                $catid = $this->getVar('startcategory');
                $order = 999;
            }

            $menuText = $adminModule['displayname'];

            // url
            try {
                $menuTextUrl = isset($adminModule['capabilities']['admin']['route']) ? $this->get('router')->generate($adminModule['capabilities']['admin']['route']) : $adminModule['capabilities']['admin']['url'];
            } catch (RouteNotFoundException $routeNotFoundException) {
                $menuTextUrl = 'javascript:void(0)';
                $menuText .= ' (<i class="fa fa-warning"></i> ' . $this->__('invalid route') . ')';
            }

            $links = $this->get('zikula.link_container_collector')->getLinks($adminModule['name'], 'admin');
            if ($links == false) {
                $links = \ModUtil::apiFunc($adminModule['name'], 'admin', 'getLinks');
                if ($links == false) {
                    $links = [];
                }
            }
            try {
                $adminIconPath = $this->get('zikula_core.common.theme.asset_helper')->resolve('@' . $adminModule['name'] . ':images/admin.png');
            } catch (\Exception $e) {
                $adminIconPath = $baseUrl . ModUtil::getModuleImagePath($adminModule['name']);
            }

            $module = [
                'menutexturl' => $menuTextUrl,
                'menutext' => $menuText,
                'menutexttitle' => $adminModule['description'],
                'modname' => $adminModule['name'],
                'order' => $order,
                'id' => $adminModule['id'],
                'links' => $links,
                'icon' => $adminIconPath
            ];

            $menuModules[$adminModule['name']] = $module;
            // category menu
            $menuCategories[$moduleCategories[$catid]['sortorder']]['title'] = $moduleCategories[$catid]['name'];
            $menuCategories[$moduleCategories[$catid]['sortorder']]['url'] = $this->get('router')->generate('zikulaadminmodule_admin_adminpanel', [
                'acid' => $moduleCategories[$catid]['cid']
            ]);
            $menuCategories[$moduleCategories[$catid]['sortorder']]['description'] = $moduleCategories[$catid]['description'];
            $menuCategories[$moduleCategories[$catid]['sortorder']]['cid'] = $moduleCategories[$catid]['cid'];
            $menuCategories[$moduleCategories[$catid]['sortorder']]['modules'][$adminModule['name']] = $module;
        }

        // add empty categories
        /** @var \Zikula\AdminModule\Entity\AdminCategoryEntity[] $moduleCategories */
        foreach ($moduleCategories as $moduleCategory) {
            if (array_key_exists($moduleCategory->getSortorder(), $menuCategories)) {
                continue;
            }

            $menuCategories[$moduleCategory->getSortOrder()] = [
                'title' => $moduleCategory->getName(),
                'url' => $this->get('router')->generate('zikulaadminmodule_admin_adminpanel', [
                    'acid' => $moduleCategory->getCid()
                ]),
                'description' => $moduleCategory->getDescription(),
                'cid' => $moduleCategory->getCid(),
                'modules' => []
            ];
        }
        ksort($menuCategories);
        $fullTemplateName = $mode . '.' . $template;

        return $this->render("@ZikulaAdminModule/AdminInterface/$fullTemplateName.html.twig", [
            'adminMenu' => ('categories' == $mode) ? $menuCategories : $menuModules,
            'mode' => $mode,
            'caller' => $caller
        ]);
    }
}
