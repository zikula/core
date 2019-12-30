<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Controller;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\AdminModule\Entity\AdminCategoryEntity;
use Zikula\AdminModule\Entity\RepositoryInterface\AdminCategoryRepositoryInterface;
use Zikula\AdminModule\Entity\RepositoryInterface\AdminModuleRepositoryInterface;
use Zikula\AdminModule\Helper\UpdateCheckHelper;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\LinkContainer\LinkContainerCollector;
use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
use Zikula\ThemeModule\Engine\Asset;

/**
 * @Route("/admininterface")
 */
class AdminInterfaceController extends AbstractController
{
    /**
     * @Route("/header")
     *
     * Open the admin container
     */
    public function headerAction(): Response
    {
        return $this->render('@ZikulaAdminModule/AdminInterface/header.html.twig', [
            'caller' => $this->get('request_stack')->getMasterRequest()->attributes->all()
        ]);
    }

    /**
     * @Route("/footer")
     *
     * Close the admin container
     */
    public function footerAction(ExtensionRepositoryInterface $extensionRepository): Response
    {
        $caller = $this->get('request_stack')->getMasterRequest()->attributes->all();
        $caller['info'] = $extensionRepository->get($caller['_zkModule']);

        return $this->render('@ZikulaAdminModule/AdminInterface/footer.html.twig', [
            'caller' => $caller,
            'symfonyVersion' => Kernel::VERSION,
            'phpVersion' => PHP_VERSION
        ]);
    }

    /**
     * @Route("/breadcrumbs", methods = {"GET"})
     *
     * Admin breadcrumbs
     */
    public function breadcrumbsAction(
        ExtensionRepositoryInterface $extensionRepository,
        AdminModuleRepositoryInterface $adminModuleRepository,
        AdminCategoryRepositoryInterface $adminCategoryRepository
    ): Response {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $masterRequest = $this->get('request_stack')->getMasterRequest();
        $caller = $masterRequest->attributes->all();
        $caller['info'] = $extensionRepository->get($caller['_zkModule']);

        $requestedCid = $masterRequest->attributes->get('acid');
        $defaultCid = empty($requestedCid) ? $this->getVar('startcategory') : $requestedCid;

        $categoryId = $defaultCid;
        if (!empty($caller['_zkModule']) && 'ZikulaAdminModule' !== $caller['_zkModule']) {
            $moduleRelation = $adminModuleRepository->findOneBy(['mid' => $caller['info']['id']]);
            if (null !== $moduleRelation) {
                $categoryId = $moduleRelation->getCid();
            }
        }
        $caller['category'] = $adminCategoryRepository->find($categoryId);

        return $this->render('@ZikulaAdminModule/AdminInterface/breadCrumbs.html.twig', [
            'caller' => $caller
        ]);
    }

    /**
     * @Route("/developernotices")
     *
     * Display developer notices
     */
    public function developernoticesAction(VariableApiInterface $variableApi): Response
    {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $modVars = $variableApi->getAll('ZikulaThemeModule');
        $data = [];
        $data['mode'] = $this->get('kernel')->getEnvironment();
        if ('prod' !== $data['mode']) {
            $data['debug'] = $this->get('kernel')->isDebug() ? $this->__('Yes') : $this->__('No');
            $data['legacy'] = [
                'status' => true,
                'cssjscombine' => $modVars['cssjscombine'],
                'render' => [
                    'compile_check' => [
                        'state' => $modVars['render_compile_check'],
                        'title' => $this->__('Compile check')
                    ],
                    'force_compile' => [
                        'state' => $modVars['render_force_compile'],
                        'title' => $this->__('Force compile')
                    ],
                    'cache' => [
                        'state' => $modVars['render_cache'],
                        'title' => $this->__('Caching')
                    ]
                ],
                'theme' => [
                    'compile_check' => [
                        'state' => $modVars['compile_check'],
                        'title' => $this->__('Compile check')
                    ],
                    'force_compile' => [
                        'state' => $modVars['force_compile'],
                        'title' => $this->__('Force compile')
                    ],
                    'cache' => [
                        'state' => $modVars['enablecache'],
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
     * Display security analyzer
     */
    public function securityanalyzerAction(Request $request, VariableApiInterface $variableApi): Response
    {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // check for .htaccess in app directory
        $appDir = $this->get('kernel')->getProjectDir() . '/app';
        if ($appDir) {
            // check if we have an absolute path which is possibly not within the document root
            $docRoot = $request->server->get('DOCUMENT_ROOT');
            if (0 === mb_strpos($appDir, '/') && false === mb_strpos($appDir, $docRoot)) {
                // temp dir is outside the webroot, no .htaccess file needed
                $app_htaccess = true;
            } else {
                if (false === mb_strpos($appDir, $docRoot)) {
                    $ldir = __DIR__;
                    $p = mb_strpos($ldir, DIRECTORY_SEPARATOR . 'system'); // we are in system/AdminModule
                    $b = mb_substr($ldir, 0, $p);
                    $filePath = $b . '/' . $appDir . '/.htaccess';
                } else {
                    $filePath = $appDir . '/.htaccess';
                }
                $app_htaccess = file_exists($filePath);
            }
        } else {
            // already customised, admin should know about what he's doing...
            $app_htaccess = true;
        }

        $hasSecurityCenter = $this->get('kernel')->isBundle('ZikulaSecurityCenterModule');

        return $this->render('@ZikulaAdminModule/AdminInterface/securityAnalyzer.html.twig', [
            'security' => [
                'app_htaccess' => $app_htaccess,
                'updatecheck' => $variableApi->getSystemVar('updatecheck'),
                'scactive' => $hasSecurityCenter,
                // check for outputfilter
                'useids' => $hasSecurityCenter && 1 === $variableApi->getSystemVar('useids'),
                'idssoftblock' => $variableApi->getSystemVar('idssoftblock')
            ]
        ]);
    }

    /**
     * @Route("/updatecheck")
     *
     * Display update check
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission for the module
     */
    public function updatecheckAction(UpdateCheckHelper $updateCheckHelper): Response
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
            'updateCheckHelper' => $updateCheckHelper
        ]);
    }

    /**
     * @Route("/menu")
     *
     * Display admin menu
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission for the module
     */
    public function menuAction(
        ExtensionRepositoryInterface $extensionRepository,
        LinkContainerCollector $linkContainerCollector,
        CapabilityApiInterface $capabilityApi,
        AdminModuleRepositoryInterface $adminModuleRepository,
        AdminCategoryRepositoryInterface $adminCategoryRepository,
        Asset $assetHelper
    ): Response {
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
        $caller['info'] = !empty($caller['_zkModule']) ? $extensionRepository->get($caller['_zkModule']) : [];

        // category we are in
        $requestedCid = $masterRequest->attributes->get('acid');
        $defaultCid = empty($requestedCid) ? $this->getVar('startcategory') : $requestedCid;

        $categoryId = $defaultCid;
        if (!empty($caller['_zkModule']) && 'ZikulaAdminModule' !== $caller['_zkModule']) {
            $moduleRelation = $adminModuleRepository->findOneBy(['mid' => $caller['info']['id']]);
            if (null !== $moduleRelation) {
                $categoryId = $moduleRelation->getCid();
            }
        }
        $caller['category'] = $adminCategoryRepository->find($categoryId);

        // mode requested
        $mode = $currentRequest->attributes->has('mode') ? $currentRequest->attributes->get('mode') : 'categories';
        $mode = in_array($mode, ['categories', 'modules']) ? $mode : 'categories';
        // template requested
        $template = $currentRequest->attributes->has('template') ? $currentRequest->attributes->get('template') : 'tabs';
        $template = in_array($template, ['tabs', 'panel']) ? $template : 'tabs';

        // get admin capable modules
        $adminModules = $capabilityApi->getExtensionsCapableOf('admin');

        // sort modules by displayname
        $moduleNames = [];
        foreach ($adminModules as $key => $module) {
            $moduleNames[$key] = $module['displayname'];
        }
        array_multisort($moduleNames, SORT_ASC, $adminModules);

        $moduleCategories = $adminCategoryRepository->getIndexedCollection('cid');
        $menuModules = [];
        $menuCategories = [];
        foreach ($adminModules as $adminModule) {
            if (!$this->hasPermission($adminModule['name'] . '::', '::', ACCESS_EDIT)) {
                continue;
            }

            $categoryAssignment = $adminModuleRepository->findOneBy(['mid' => $adminModule['id']]);
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
                $menuText .= ' (<i class="fa fa-exclamation-triangle"></i> ' . $this->__('invalid route') . ')';
            }

            $moduleName = (string)$adminModule['name'];
            $links = $linkContainerCollector->getLinks($moduleName, 'admin');
            try {
                $adminIconPath = $assetHelper->resolve('@' . $adminModule['name'] . ':images/admin.png');
            } catch (Exception $exception) {
                // use default icon
                $adminIconPath = $assetHelper->resolve('bundles/core/images/admin.png');
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
            if (!$this->hasPermission('ZikulaAdminModule:Category:', $moduleCategories[$catid]['name'] . '::' . $moduleCategories[$catid]['cid'], ACCESS_ADMIN)) {
                continue;
            }

            $categorySortOrder = $moduleCategories[$catid]['sortorder'];
            $menuCategories[$categorySortOrder]['title'] = $moduleCategories[$catid]['name'];
            $menuCategories[$categorySortOrder]['url'] = $this->get('router')->generate('zikulaadminmodule_admin_adminpanel', [
                'acid' => $moduleCategories[$catid]['cid']
            ]);
            $menuCategories[$categorySortOrder]['description'] = $moduleCategories[$catid]['description'];
            $menuCategories[$categorySortOrder]['cid'] = $moduleCategories[$catid]['cid'];
            $menuCategories[$categorySortOrder]['modules'][$adminModule['name']] = $module;
        }

        // add empty categories
        /** @var AdminCategoryEntity[] $moduleCategories */
        foreach ($moduleCategories as $moduleCategory) {
            if (array_key_exists($moduleCategory->getSortorder(), $menuCategories)) {
                continue;
            }
            if (!$this->hasPermission('ZikulaAdminModule:Category:', $moduleCategory->getName() . '::' . $moduleCategory->getCid(), ACCESS_ADMIN)) {
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

        return $this->render("@ZikulaAdminModule/AdminInterface/${fullTemplateName}.html.twig", [
            'adminMenu' => ('categories' === $mode) ? $menuCategories : $menuModules,
            'mode' => $mode,
            'caller' => $caller
        ]);
    }
}
