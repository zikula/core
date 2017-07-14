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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\AdminModule\Entity\AdminCategoryEntity;
use Zikula\AdminModule\Form\Type\CreateCategoryType;
use Zikula\AdminModule\Form\Type\DeleteCategoryType;
use Zikula\AdminModule\Form\Type\EditCategoryType;
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
     * The main administration function.
     *
     * This function is the default function, and is called whenever the
     * module is initiated without defining arguments.  As such it can
     * be used for a number of things, but most commonly it either just
     * shows the module menu and returns or calls whatever the module
     * designer feels should be the default function (often this is the
     * view() function)
     *
     * @Route("")
     *
     * @return RedirectResponse symfony response object
     */
    public function indexAction()
    {
        // Security check will be done in view()
        return $this->redirectToRoute('zikulaadminmodule_admin_view');
    }

    /**
     * Views all admin categories.
     *
     * @Route("/categories/{startnum}", requirements={"startnum" = "\d+"})
     * @Method("GET")
     * @Theme("admin")
     * @Template
     *
     * @param integer $startnum
     *
     * @return array
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
        /** @var \Doctrine\ORM\Tools\Pagination\Paginator $items */
        $items = $this->get('doctrine')->getRepository('ZikulaAdminModule:AdminCategoryEntity')->getPagedCategories(['sortorder' => 'ASC'], $itemsPerPage, $startnum);

        foreach ($items as $item) {
            if ($this->hasPermission('ZikulaAdminModule::', $item['name'] . '::' . $item['cid'], ACCESS_READ)) {
                $categories[] = $item;
            }
        }

        return [
            'categories' => $categories,
            'pager' => [
                'amountOfItems' => $items->count(),
                'itemsPerPage' => $itemsPerPage
            ]
        ];
    }

    /**
     * Displays a new admin category form.
     *
     * Displays a form for the user to input the details of the new category. Data is supplied to @see this::createAction()
     *
     * @Route("/newcategory")
     * @Theme("admin")
     * @Template
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to add a category
     */
    public function newcatAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaAdminModule::Item', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(CreateCategoryType::class, new AdminCategoryEntity(), [
            'translator' => $this->get('translator.default')
        ]);

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                $adminCategory = $form->getData();
                $this->get('doctrine')->getManager()->persist($adminCategory);
                $this->get('doctrine')->getManager()->flush();
                $this->addFlash('status', $this->__('Done! Created new category.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }
            if ($form->get('help')->isClicked()) {
                return $this->redirect($this->generateUrl('zikulaadminmodule_admin_view') . '#new');
            }

            return $this->redirectToRoute('zikulaadminmodule_admin_view');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * Displays a modify category form.
     *
     * @Route("/modifycategory/{cid}", requirements={"cid" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template
     *
     * @param Request $request
     *
     * @param AdminCategoryEntity $category
     * @return array|RedirectResponse
     */
    public function modifyAction(Request $request, AdminCategoryEntity $category)
    {
        if (!$this->hasPermission('ZikulaAdminModule::Category', $category['name'] . '::' . $category->getCid(), ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(EditCategoryType::class, $category, [
            'translator' => $this->get('translator.default')
        ]);

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                $category = $form->getData();
                if (!$this->hasPermission('ZikulaAdminModule::Category', $category->getName() . '::' . $category->getCid(), ACCESS_EDIT)) {
                    throw new AccessDeniedException();
                }
                $this->get('doctrine')->getManager()->flush();
                $this->addFlash('status', $this->__('Done! Saved category.'));
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
     * Deletes an admin category.
     *
     * @Route("/deletecategory/{cid}", requirements={"cid" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template
     *
     * @param Request $request
     * @param AdminCategoryEntity $category
     * @return array|RedirectResponse
     */
    public function deleteAction(Request $request, AdminCategoryEntity $category)
    {
        if (!$this->hasPermission('ZikulaAdminModule::Category', $category->getName() . "::" . $category->getCid(), ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(DeleteCategoryType::class, $category, [
            'translator' => $this->get('translator.default')
        ]);

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $category = $form->getData();
                $this->get('doctrine')->getManager()->remove($category);
                $this->get('doctrine')->getManager()->flush();
                $this->addFlash('status', $this->__('Done! Category deleted.'));
            } elseif ($form->get('cancel')->isClicked()) {
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
     * Displays main admin panel for a category.
     *
     * @Route("/panel/{acid}", requirements={"acid" = "^[1-9]\d*$"})
     * @Method("GET")
     * @Theme("admin")
     * @Template
     *
     * @param Request $request
     * @param integer $acid
     * @return Response symfony response object
     */
    public function adminpanelAction(Request $request, $acid = null)
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
            if (true === $zrcExists) {
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
            'menu' => $this->categorymenuAction($request, $acid)->getContent()
        ];

        // Check to see if we have access to the requested category.
        if (!$this->hasPermission('ZikulaAdminModule::', "::$acid", ACCESS_ADMIN)) {
            $acid = -1;
        }

        $entityManager = $this->get('doctrine')->getManager();
        $adminCategoryRepository = $entityManager->getRepository('ZikulaAdminModule:AdminCategoryEntity');

        // Get details for selected category
        $category = null;
        if ($acid > 0) {
            $category = $adminCategoryRepository->findOneBy(['cid' => $acid]);
        }

        if (!$category) {
            // get the default category
            $acid = $this->getVar('startcategory');

            // Check to see if we have access to the requested category.
            if (!$this->hasPermission('ZikulaAdminModule::', "::$acid", ACCESS_ADMIN)) {
                throw new AccessDeniedException();
            }

            $category = $adminCategoryRepository->findOneBy(['cid' => $acid]);
        }

        // assign the category
        $templateParameters['category'] = $category;

        $displayNameType = $this->getVar('displaynametype', 1);

        $adminModuleRepository = $entityManager->getRepository('ZikulaAdminModule:AdminModuleEntity');
        $moduleEntities = $adminModuleRepository->findAll();

        // get admin capable modules
        $adminModules = $this->get('zikula_extensions_module.api.capability')->getExtensionsCapableOf('admin');
        $adminLinks = [];
        foreach ($adminModules as $adminModule) {
            if (!$this->hasPermission($adminModule['name'] . '::', 'ANY', ACCESS_EDIT)) {
                continue;
            }

            $moduleCategory = $adminCategoryRepository->getModuleCategory($adminModule['id']);
            $catid = $moduleCategory['cid'];

            $sortOrder = -1;
            foreach ($moduleEntities as $association) {
                if ($association['mid'] != $adminModule['id']) {
                    continue;
                }

                $sortOrder = $association['sortorder'];
                break;
            }

            if ($catid == $acid || (false === $catid && $acid == $this->getVar('defaultcategory'))) {
                $menuText = '';
                if ($displayNameType == 1) {
                    $menuText = $adminModule['displayname'];
                } elseif ($displayNameType == 2) {
                    $menuText = $adminModule['name'];
                } elseif ($displayNameType == 3) {
                    $menuText = $adminModule['displayname'] . ' (' . $adminModule['name'] . ')';
                }

                try {
                    $menuTextUrl = $this->get('router')->generate($adminModule['capabilities']['admin']['route']);
                } catch (RouteNotFoundException $routeNotFoundException) {
                    $menuTextUrl = 'javascript:void(0)';
                    $menuText .= ' (<i class="fa fa-warning"></i> ' . $this->__('invalid route') . ')';
                }

                $links = $this->get('zikula.link_container_collector')->getLinks($adminModule['name'], 'admin');
                $adminIconPath = $this->get('zikula_core.common.theme.asset_helper')->resolve('@' . $adminModule['name'] . ':images/admin.png');

                $adminLinks[] = [
                    'menuTextUrl' => $menuTextUrl,
                    'menuText' => $menuText,
                    'menuTextTitle' => $adminModule['description'],
                    'moduleName' => $adminModule['name'],
                    'adminIcon' => $adminIconPath,
                    'id' => $adminModule['id'],
                    'order' => $sortOrder,
                    'links' => $links
                ];
            }
        }
        usort($adminLinks, 'Zikula\AdminModule\Controller\AdminController::sortAdminModsByOrder');
        $templateParameters['adminLinks'] = $adminLinks;

        return $templateParameters;
    }

    /**
     * Displays main category menu.
     *
     * @Route("/categorymenu/{acid}", requirements={"acid" = "^[1-9]\d*$"})
     * @Method("GET")
     * @Theme("admin")
     *
     * @param Request $request
     * @param integer $acid
     * @return Response symfony response object
     */
    public function categorymenuAction(Request $request, $acid = null)
    {
        $acid = empty($acid) ? $this->getVar('startcategory') : $acid;

        $entityManager = $this->get('doctrine')->getManager();
        $adminCategoryRepository = $entityManager->getRepository('ZikulaAdminModule:AdminCategoryEntity');

        // Get all categories
        $categories = [];
        $items = $adminCategoryRepository->findBy([], ['sortorder' => 'ASC']);
        foreach ($items as $item) {
            if ($this->hasPermission('ZikulaAdminModule::', "$item[name]::$item[cid]", ACCESS_READ)) {
                $categories[] = $item;
            }
        }

        $adminModuleRepository = $entityManager->getRepository('ZikulaAdminModule:AdminModuleEntity');
        $moduleEntities = $adminModuleRepository->findAll();

        // get admin capable modules
        $adminModules = $this->get('zikula_extensions_module.api.capability')->getExtensionsCapableOf('admin');
        $adminLinks = [];
        foreach ($adminModules as $adminModule) {
            if (!$this->hasPermission($adminModule['name'] . '::', '::', ACCESS_EDIT)) {
                continue;
            }

            $menuText = $adminModule['displayname'];
            try {
                $menuTextUrl = $this->get('router')->generate($adminModule['capabilities']['admin']['route']);
            } catch (RouteNotFoundException $routeNotFoundException) {
                $menuTextUrl = 'javascript:void(0)';
                $menuText .= ' (<i class="fa fa-warning"></i> ' . $this->__('invalid route') . ')';
            }

            $moduleCategory = $adminCategoryRepository->getModuleCategory($adminModule['id']);
            $catid = $moduleCategory['cid'];

            $sortOrder = -1;
            foreach ($moduleEntities as $association) {
                if ($association['mid'] != $adminModule['id']) {
                    continue;
                }

                $sortOrder = $association['sortorder'];
                break;
            }

            $adminIconPath = $this->get('zikula_core.common.theme.asset_helper')->resolve('@' . $adminModule['name'] . ':images/admin.png');

            $adminLinks[$catid][] = [
                'menuTextUrl' => $menuTextUrl,
                'menuText' => $menuText,
                'menuTextTitle' => $adminModule['description'],
                'moduleName' => $adminModule['name'],
                'order' => $sortOrder,
                'id' => $adminModule['id'],
                'icon' => $adminIconPath
            ];
        }

        foreach ($adminLinks as &$item) {
            usort($item, 'Zikula\AdminModule\Controller\AdminController::sortAdminModsByOrder');
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
        if (false === $permission) {
            // show the first category
            $acid = !empty($possibleCategoryIds) ? (int)$possibleCategoryIds[0] : null;
        }

        return $this->render('@ZikulaAdminModule/Admin/categoryMenu.html.twig', [
            'currentCategory' => $acid,
            'menuOptions' => $menuOptions
        ]);
    }

    /**
     * Opens the admin container.
     *
     * @Route("/header")
     *
     * @return Response symfony response object
     */
    public function adminheaderAction()
    {
        return $this->render('@ZikulaAdminModule/Admin/header.html.twig');
    }

    /**
     * Closes the admin container.
     *
     * @Route("/footer")
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
     * Displays the module's help page.
     *
     * @Route("/help")
     * @Theme("admin")
     * @Template
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
     * Helper function to sort modules.
     *
     * @param $a array first item to compare
     * @param $b array second item to compare
     *
     * @return int < 0 if module a should be ordered before module b > 0 otherwise
     */
    private static function sortAdminModsByOrder($a, $b)
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
