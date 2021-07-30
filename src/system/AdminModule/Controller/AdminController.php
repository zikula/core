<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\AdminModule\Entity\AdminCategoryEntity;
use Zikula\AdminModule\Entity\RepositoryInterface\AdminCategoryRepositoryInterface;
use Zikula\AdminModule\Entity\RepositoryInterface\AdminModuleRepositoryInterface;
use Zikula\AdminModule\Form\Type\AdminCategoryType;
use Zikula\AdminModule\Helper\AdminLinksHelper;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\CoreBundle\Doctrine\PaginatorInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DeletionType;
use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuCollector;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuInterface;
use Zikula\PermissionsModule\Annotation\PermissionCheck;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Administrative controllers for the admin module
 * NOTE: intentionally no class level route setting here
 */
class AdminController extends AbstractController
{
    /**
     * @Route("")
     *
     * The main administration function.
     */
    public function index(): RedirectResponse
    {
        // Security check will be done in view()
        return $this->redirectToRoute('zikulaadminmodule_admin_view');
    }

    /**
     * @Route("/categories/{page}", methods = {"GET"}, requirements={"page" = "\d+"})
     * @PermissionCheck("edit")
     * @Theme("admin")
     * @Template("@ZikulaAdminModule/Admin/view.html.twig")
     *
     * Views all admin categories.
     */
    public function view(AdminCategoryRepositoryInterface $repository, int $page = 1): array
    {
        $pageSize = (int) $this->getVar('itemsperpage');

        /** @var PaginatorInterface $paginator */
        $paginator = $repository->getPagedCategories(['sortorder' => 'ASC'], $page, $pageSize);
        $paginator->setRoute('zikulaadminmodule_admin_view');

        return [
            'paginator' => $paginator
        ];
    }

    /**
     * @Route("/newcategory")
     * @PermissionCheck({"$_zkModule::Item", "::", "add"})
     * @Theme("admin")
     * @Template("@ZikulaAdminModule/Admin/editCategory.html.twig")
     *
     * Displays and handles a form for the user to input the details of the new category.
     *
     * @return array|RedirectResponse
     */
    public function create(Request $request)
    {
        $form = $this->createForm(AdminCategoryType::class, new AdminCategoryEntity());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $adminCategory = $form->getData();
                $this->getDoctrine()->getManager()->persist($adminCategory);
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('status', 'Done! Created new category.');
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
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
     * @Template("@ZikulaAdminModule/Admin/editCategory.html.twig")
     *
     * Displays and handles a modify category form.
     *
     * @return array|RedirectResponse
     * @throws AccessDeniedException Thrown if the user doesn't have permission to edit a category
     */
    public function modify(Request $request, AdminCategoryEntity $category)
    {
        if (!$this->hasPermission('ZikulaAdminModule::Category', $category['name'] . '::' . $category->getCid(), ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(AdminCategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $category = $form->getData();
                if (!$this->hasPermission('ZikulaAdminModule::Category', $category->getName() . '::' . $category->getCid(), ACCESS_EDIT)) {
                    throw new AccessDeniedException();
                }
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('status', 'Done! Saved category.');
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulaadminmodule_admin_view');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/deletecategory/{cid}", requirements={"cid" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template("@ZikulaAdminModule/Admin/delete.html.twig")
     *
     * Deletes an admin category.
     *
     * @return array|RedirectResponse
     * @throws AccessDeniedException Thrown if the user doesn't have permission to edit a category
     */
    public function delete(Request $request, AdminCategoryEntity $category)
    {
        if (!$this->hasPermission('ZikulaAdminModule::Category', $category->getName() . '::' . $category->getCid(), ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(DeletionType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $category = $form->getData();
                $this->getDoctrine()->getManager()->remove($category);
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('status', 'Done! Category deleted.');
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulaadminmodule_admin_view');
        }

        return [
            'form' => $form->createView(),
            'category' => $category
        ];
    }

    /**
     * @Route("/panel/{acid}", methods = {"GET"}, requirements={"acid" = "^[1-9]\d*$"})
     * @PermissionCheck("edit")
     * @Theme("admin")
     * @Template("@ZikulaAdminModule/Admin/adminpanel.html.twig")
     *
     * Displays main admin panel for a category.
     *
     * @return array|Response
     * @throws AccessDeniedException Thrown if the user doesn't have edit permission for the module
     */
    public function adminpanel(
        ZikulaHttpKernelInterface $kernel,
        AdminCategoryRepositoryInterface $adminCategoryRepository,
        AdminModuleRepositoryInterface $adminModuleRepository,
        CapabilityApiInterface $capabilityApi,
        RouterInterface $router,
        AdminLinksHelper $adminLinksHelper,
        ExtensionMenuCollector $extensionMenuCollector,
        int $acid = null
    ) {
        if (empty($acid)) {
            $acid = $this->getVar('startcategory', 1);
        }

        // Check to see if we have access to the requested category.
        if (!$this->hasPermission('ZikulaAdminModule::', "::${acid}", ACCESS_ADMIN)) {
            $acid = -1;
        }

        // Get details for selected category
        $category = null;
        if ($acid > 0) {
            $category = $adminCategoryRepository->findOneBy(['cid' => $acid]);
        }

        if (!$category) {
            // get the default category
            $acid = $this->getVar('startcategory');

            // Check to see if we have access to the requested category.
            if (!$this->hasPermission('ZikulaAdminModule::', "::${acid}", ACCESS_ADMIN)) {
                throw new AccessDeniedException();
            }

            $category = $adminCategoryRepository->findOneBy(['cid' => $acid]);
        }

        $templateParameters = [];

        // assign the category
        $templateParameters['category'] = $category;

        $displayNameType = $this->getVar('displaynametype', 1);
        $moduleEntities = $adminModuleRepository->findAll();

        // get admin capable modules
        $adminModules = $capabilityApi->getExtensionsCapableOf('admin');
        $adminLinks = [];
        foreach ($adminModules as $adminModule) {
            if (!$this->hasPermission($adminModule['name'] . '::', 'ANY', ACCESS_EDIT)) {
                continue;
            }

            $moduleId = (int) $adminModule['id'];
            $moduleCategory = $adminCategoryRepository->getModuleCategory($moduleId);
            $catid = null !== $moduleCategory ? $moduleCategory['cid'] : 0;

            $sortOrder = -1;
            foreach ($moduleEntities as $association) {
                if ($association['mid'] !== $adminModule['id']) {
                    continue;
                }

                $sortOrder = $association['sortorder'];
                break;
            }

            if ($catid === $acid || (false === $catid && $acid === $this->getVar('defaultcategory'))) {
                $menuText = '';
                if (1 === $displayNameType) {
                    $menuText = $adminModule['displayname'];
                } elseif (2 === $displayNameType) {
                    $menuText = $adminModule['name'];
                } elseif (3 === $displayNameType) {
                    $menuText = $adminModule['displayname'] . ' (' . $adminModule['name'] . ')';
                }

                try {
                    $menuTextUrl = isset($adminModule['capabilities']['admin']['route'])
                        ? $router->generate($adminModule['capabilities']['admin']['route'])
                        : '';
                } catch (RouteNotFoundException $routeNotFoundException) {
                    $menuTextUrl = 'javascript:void(0)';
                    $menuText .= ' (<i class="fas fa-exclamation-triangle"></i> ' . $this->trans('invalid route') . ')';
                }

                $moduleName = (string) $adminModule['name'];
                /** @var \Knp\Menu\ItemInterface $extensionMenu */
                $extensionMenu = $extensionMenuCollector->get($moduleName, ExtensionMenuInterface::TYPE_ADMIN);
                if (isset($extensionMenu)) {
                    $extensionMenu->setChildrenAttribute('class', 'dropdown-menu');
                }

                $adminLinks[] = [
                    'menuTextUrl' => $menuTextUrl,
                    'menuText' => $menuText,
                    'menuTextTitle' => $adminModule['description'],
                    'moduleName' => $adminModule['name'],
                    'adminIcon' => $adminModule['icon'],
                    'id' => $adminModule['id'],
                    'order' => $sortOrder,
                    'extensionMenu' => $extensionMenu
                ];
            }
        }
        $templateParameters['adminLinks'] = $adminLinksHelper->sortAdminModsByOrder($adminLinks);

        return $templateParameters;
    }

    /**
     * @Route("/categorymenu/{acid}", methods = {"GET"}, requirements={"acid" = "^[1-9]\d*$"})
     * @Theme("admin")
     *
     * Displays main category menu.
     */
    public function categorymenu(
        AdminCategoryRepositoryInterface $adminCategoryRepository,
        AdminModuleRepositoryInterface $adminModuleRepository,
        CapabilityApiInterface $capabilityApi,
        RouterInterface $router,
        AdminLinksHelper $adminLinksHelper,
        int $acid = null
    ): Response {
        $acid = empty($acid) ? $this->getVar('startcategory') : $acid;

        // Get all categories
        $categories = [];
        $items = $adminCategoryRepository->findBy([], ['sortorder' => 'ASC']);
        foreach ($items as $item) {
            if ($this->hasPermission('ZikulaAdminModule::', $item['name'] . '::' . $item['cid'], ACCESS_READ)) {
                $categories[] = $item;
            }
        }

        $moduleEntities = $adminModuleRepository->findAll();

        // get admin capable modules
        $adminModules = $capabilityApi->getExtensionsCapableOf('admin');
        $adminLinks = [];
        foreach ($adminModules as $adminModule) {
            if (!$this->hasPermission($adminModule['name'] . '::', '::', ACCESS_EDIT)) {
                continue;
            }

            $menuText = $adminModule['displayname'];
            try {
                $menuTextUrl = isset($adminModule['capabilities']['admin']['route'])
                    ? $router->generate($adminModule['capabilities']['admin']['route'])
                    : '';
            } catch (RouteNotFoundException $routeNotFoundException) {
                $menuTextUrl = 'javascript:void(0)';
                $menuText .= ' (<i class="fas fa-exclamation-triangle"></i> ' . $this->trans('invalid route') . ')';
            }

            $moduleId = (int) $adminModule['id'];
            $moduleCategory = $adminCategoryRepository->getModuleCategory($moduleId);
            $catid = null !== $moduleCategory ? $moduleCategory['cid'] : 0;

            $sortOrder = -1;
            foreach ($moduleEntities as $association) {
                if ($association['mid'] !== $adminModule['id']) {
                    continue;
                }

                $sortOrder = $association['sortorder'];
                break;
            }

            $adminLinks[$catid][] = [
                'menuTextUrl' => $menuTextUrl,
                'menuText' => $menuText,
                'menuTextTitle' => $adminModule['description'],
                'moduleName' => $adminModule['name'],
                'order' => $sortOrder,
                'id' => $adminModule['id'],
                'icon' => $adminModule['icon']
            ];
        }

        foreach ($adminLinks as $categoryId => $links) {
            $adminLinks[$categoryId] = $adminLinksHelper->sortAdminModsByOrder($links);
        }

        $menuOptions = [];
        $possibleCategoryIds = [];
        $permission = false;

        if (isset($categories) && is_array($categories)) {
            foreach ($categories as $category) {
                // only categories containing modules where the current user has permissions will
                // be shown, all others will be hidden
                // admin will see all categories
                if ($this->hasPermission('.*', '.*', ACCESS_ADMIN)
                    || (isset($adminLinks[$category['cid']]) && count($adminLinks[$category['cid']]))
                ) {
                    $menuOption = [
                        'url' => $router->generate('zikulaadminmodule_admin_adminpanel', ['acid' => $category['cid']]),
                        'title' => $category['name'],
                        'description' => $category['description'],
                        'cid' => $category['cid'],
                        'items' => $adminLinks[$category['cid']] ?? []
                    ];

                    $menuOptions[$category['cid']] = $menuOption;
                    $possibleCategoryIds[] = $category['cid'];

                    if ($acid === $category['cid']) {
                        $permission = true;
                    }
                }
            }
        }

        // if permission is false we are not allowed to see this category because its
        // empty and we are not admin
        if (false === $permission) {
            // show the first category
            $acid = !empty($possibleCategoryIds) ? (int) $possibleCategoryIds[0] : null;
        }

        return $this->render('@ZikulaAdminModule/Admin/categoryMenu.html.twig', [
            'currentCategory' => $acid,
            'menuOptions' => $menuOptions
        ]);
    }
}
