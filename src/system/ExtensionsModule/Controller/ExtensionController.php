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

namespace Zikula\ExtensionsModule\Controller;

use Psr\Log\LoggerInterface;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\BlocksModule\Entity\RepositoryInterface\BlockRepositoryInterface;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DeletionType;
use Zikula\Component\SortableColumns\Column;
use Zikula\Component\SortableColumns\SortableColumns;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ExtensionsModule\Constant;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
use Zikula\ExtensionsModule\Event\ExtensionListPreReSyncEvent;
use Zikula\ExtensionsModule\Form\Type\ExtensionModifyType;
use Zikula\ExtensionsModule\Helper\BundleSyncHelper;
use Zikula\ExtensionsModule\Helper\ExtensionDependencyHelper;
use Zikula\ExtensionsModule\Helper\ExtensionHelper;
use Zikula\ExtensionsModule\Helper\ExtensionStateHelper;
use Zikula\PermissionsModule\Annotation\PermissionCheck;
use Zikula\RoutesModule\Event\RoutesNewlyAvailableEvent;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\ThemeModule\Engine\Engine;

/**
 * @Route("")
 */
class ExtensionController extends AbstractController
{
    /**
     * @Route("/list/{page}", methods = {"GET"}, requirements={"page" = "\d+"})
     * @PermissionCheck("admin")
     * @Theme("admin")
     * @Template("@ZikulaExtensionsModule/Extension/list.html.twig")
     */
    public function listAction(
        Request $request,
        EventDispatcherInterface $eventDispatcher,
        ExtensionRepositoryInterface $extensionRepository,
        BundleSyncHelper $bundleSyncHelper,
        RouterInterface $router,
        int $page = 1
    ): array {
        $modulesJustInstalled = $request->query->get('justinstalled');
        if (!empty($modulesJustInstalled)) {
            // notify the event dispatcher that new routes are available (ids of modules just installed avail as args)
            $eventDispatcher->dispatch(new RoutesNewlyAvailableEvent(json_decode($modulesJustInstalled)));
        }

        $sortableColumns = new SortableColumns($router, 'zikulaextensionsmodule_extension_list');
        $sortableColumns->addColumns([new Column('displayname'), new Column('state')]);
        $sortableColumns->setOrderByFromRequest($request);

        $upgradedExtensions = [];
        $extensionListPreReSyncEvent = new ExtensionListPreReSyncEvent();
        $eventDispatcher->dispatch($extensionListPreReSyncEvent);
        if (1 === $page && !$extensionListPreReSyncEvent->isPropagationStopped()) {
            // regenerate the extension list only when viewing the first page
            $extensionsInFileSystem = $bundleSyncHelper->scanForBundles();
            $upgradedExtensions = $bundleSyncHelper->syncExtensions($extensionsInFileSystem);
        }

        $pageSize = $this->getVar('itemsperpage');

        $paginator = $extensionRepository->getPagedCollectionBy([], [
            $sortableColumns->getSortColumn()->getName() => $sortableColumns->getSortDirection()
        ], $page, $pageSize);
        $paginator->setRoute('zikulaextensionsmodule_extension_list');

        return [
            'sort' => $sortableColumns->generateSortableColumns(),
            'paginator' => $paginator,
            'upgradedExtensions' => $upgradedExtensions
        ];
    }

    /**
     * @Route("/activate/{id}/{token}", methods = {"GET"}, requirements={"id" = "^[1-9]\d*$"})
     * @PermissionCheck("admin")
     *
     * Activate an extension.
     *
     * @throws AccessDeniedException Thrown if the CSRF token is invalid
     */
    public function activateAction(
        int $id,
        string $token,
        ExtensionRepositoryInterface $extensionRepository,
        ExtensionStateHelper $extensionStateHelper,
        LoggerInterface $zikulaLogger
    ): RedirectResponse {
        if (!$this->isCsrfTokenValid('activate-extension', $token)) {
            throw new AccessDeniedException();
        }

        /** @var ExtensionEntity $extension */
        $extension = $extensionRepository->find($id);
        $zikulaLogger->notice('Extension activating', ['name' => $extension->getName()]);
        if (Constant::STATE_NOTALLOWED === $extension->getState()) {
            $this->addFlash('error', $this->trans('Error! Activation of %name% not allowed.', ['%name%' => $extension->getName()]));
        } else {
            $extensionStateHelper->updateState($id, Constant::STATE_ACTIVE);
            $this->addFlash('status', $this->trans('Done! Activated %name%.', ['%name%' => $extension->getName()]));
        }

        return $this->redirectToRoute('zikulaextensionsmodule_extension_list');
    }

    /**
     * @Route("/deactivate/{id}/{token}", methods = {"GET"}, requirements={"id" = "^[1-9]\d*$"})
     * @PermissionCheck("admin")
     *
     * Deactivate an extension
     *
     * @throws AccessDeniedException Thrown if the CSRF token is invalid
     */
    public function deactivateAction(
        int $id,
        string $token,
        ExtensionRepositoryInterface $extensionRepository,
        ExtensionStateHelper $extensionStateHelper,
        LoggerInterface $zikulaLogger
    ): RedirectResponse {
        if (!$this->isCsrfTokenValid('deactivate-extension', $token)) {
            throw new AccessDeniedException();
        }

        /** @var ExtensionEntity $extension */
        $extension = $extensionRepository->find($id);
        $zikulaLogger->notice('Extension deactivating', ['name' => $extension->getName()]);
        $defaultTheme = $this->getVariableApi()->get(VariableApi::CONFIG, 'defaulttheme');
        $adminTheme = $this->getVariableApi()->get('ZikulaAdminModule', 'admintheme');

        if (null !== $extension) {
            if (ZikulaKernel::isCoreExtension($extension->getName())) {
                $this->addFlash('error', $this->trans('Error! You cannot deactivate the %name%. It is required by the system.', ['%name%' => $extension->getName()]));
            } elseif (in_array($extension->getName(), [$defaultTheme, $adminTheme])) {
                $this->addFlash('error', $this->trans('Error! You cannot deactivate the %name%. The theme is in use.', ['%name%' => $extension->getName()]));
            } else {
                $extensionStateHelper->updateState($id, Constant::STATE_INACTIVE);
                $this->addFlash('status', $this->trans('Done! Deactivated %name%.', ['%name%' => $extension->getName()]));
            }
        }

        return $this->redirectToRoute('zikulaextensionsmodule_extension_list');
    }

    /**
     * @Route("/modify/{id}/{forceDefaults}", requirements={"id" = "^[1-9]\d*$", "forceDefaults" = "0|1"})
     * @Theme("admin")
     * @Template("@ZikulaExtensionsModule/Extension/modify.html.twig")
     *
     * Modify a module.
     *
     * @return array|RedirectResponse
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions for modifying the extension
     */
    public function modifyAction(
        Request $request,
        ZikulaHttpKernelInterface $kernel,
        ExtensionEntity $extension,
        CacheClearer $cacheClearer,
        bool $forceDefaults = false
    ) {
        if (!$this->hasPermission('ZikulaExtensionsModule::modify', $extension->getName() . '::' . $extension->getId(), ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        /** @var AbstractExtension $extensionBundle */
        $extensionBundle = $kernel->getBundle($extension->getName());
        $metaData = $extensionBundle->getMetaData()->getFilteredVersionInfoArray();

        if ($forceDefaults) {
            $extension->setName($metaData['name']);
            $extension->setUrl($metaData['url']);
            $extension->setDescription($metaData['description']);
        }

        $formOptions = [
            'extensionType' => mb_strtolower($extensionBundle->getNameType())
        ];

        $form = $this->createForm(ExtensionModifyType::class, $extension, $formOptions);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('defaults')->isClicked()) {
                $this->addFlash('info', 'Default values reloaded. Save to confirm.');

                return $this->redirectToRoute('zikulaextensionsmodule_extension_modify', ['id' => $extension->getId(), 'forceDefaults' => 1]);
            }
            if ($form->get('save')->isClicked()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($extension);
                $em->flush();

                $cacheClearer->clear('symfony.routing');
                $this->addFlash('status', 'Done! Extension updated.');
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulaextensionsmodule_extension_list');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/compatibility/{id}", methods = {"GET"}, requirements={"id" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template("@ZikulaExtensionsModule/Extension/compatibility.html.twig")
     *
     * Display information of a module compatibility with the version of the core
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission to the requested module
     */
    public function compatibilityAction(ExtensionEntity $extension): array
    {
        if (!$this->hasPermission('ZikulaExtensionsModule::', $extension->getName() . '::' . $extension->getId(), ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        return [
            'extension' => $extension
        ];
    }

    /**
     * @Route("/upgrade/{id}/{token}", requirements={"id" = "^[1-9]\d*$"})
     * @PermissionCheck("admin")
     *
     * Upgrade an extension.
     *
     * @throws AccessDeniedException Thrown if the CSRF token is invalid
     */
    public function upgradeAction(
        ExtensionEntity $extension,
        $token,
        ExtensionHelper $extensionHelper
    ): RedirectResponse {
        if (!$this->isCsrfTokenValid('upgrade-extension', $token)) {
            throw new AccessDeniedException();
        }

        $result = $extensionHelper->upgrade($extension);
        if ($result) {
            $this->addFlash('status', $this->trans('%name% upgraded to new version and activated.', ['%name%' => $extension->getDisplayname()]));
        } else {
            $this->addFlash('error', 'Extension upgrade failed!');
        }

        return $this->redirectToRoute('zikulaextensionsmodule_extension_list');
    }

    /**
     * @Route("/uninstall/{id}/{token}", requirements={"id" = "^[1-9]\d*$"})
     * @PermissionCheck("admin")
     * @Theme("admin")
     * @Template("@ZikulaExtensionsModule/Extension/uninstall.html.twig")
     *
     * Uninstall an extension.
     *
     * @return array|Response|RedirectResponse
     * @throws AccessDeniedException Thrown if the CSRF token is invalid
     */
    public function uninstallAction(
        Request $request,
        ExtensionEntity $extension,
        string $token,
        ZikulaHttpKernelInterface $kernel,
        BlockRepositoryInterface $blockRepository,
        ExtensionHelper $extensionHelper,
        ExtensionStateHelper $extensionStateHelper,
        ExtensionDependencyHelper $dependencyHelper
    ) {
        if (!$this->isCsrfTokenValid('uninstall-extension', $token)) {
            throw new AccessDeniedException();
        }

        if (Constant::STATE_MISSING === $extension->getState()) {
            throw new RuntimeException($this->trans('Error! The requested extension cannot be uninstalled because its files are missing!'));
        }
        if (!$kernel->isBundle($extension->getName())) {
            $extensionStateHelper->updateState($extension->getId(), Constant::STATE_TRANSITIONAL);
        }
        $requiredDependents = $dependencyHelper->getDependentExtensions($extension);
        $blocks = $blockRepository->findBy(['module' => $extension]);

        $form = $this->createForm(DeletionType::class, [], [
            'action' => $this->generateUrl('zikulaextensionsmodule_extension_uninstall', [
                'id' => $extension->getId(),
                'token' => $token
            ]),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                // remove dependent extensions
                if (!$extensionHelper->uninstallArray($requiredDependents)) {
                    $this->addFlash('error', 'Error: Could not uninstall dependent extensions.');

                    return $this->redirectToRoute('zikulaextensionsmodule_extension_list');
                }
                // remove blocks
                $blockRepository->remove($blocks);

                // remove the extension
                if ($extensionHelper->uninstall($extension)) {
                    $this->addFlash('status', 'Done! Uninstalled extension.');
                } else {
                    $this->addFlash('error', 'Extension removal failed! (note: blocks and dependents may have been removed)');
                }
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulaextensionsmodule_extension_postuninstall');
        }

        return [
            'form' => $form->createView(),
            'extension' => $extension,
            'blocks' => $blocks,
            'requiredDependents' => $requiredDependents
        ];
    }

    /**
     * @Route("/post-uninstall")
     * @PermissionCheck("admin")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function postUninstallAction(CacheClearer $cacheClearer)
    {
        $cacheClearer->clear('symfony');

        return $this->redirectToRoute('zikulaextensionsmodule_extension_list');
    }

    /**
     * @Route("/theme-preview/{themeName}")
     * @PermissionCheck("admin")
     */
    public function previewAction(Engine $engine, string $themeName): Response
    {
        $engine->setActiveTheme($themeName);
        $this->addFlash('warning', 'Please note that blocks may appear out of place or even missing in a theme preview because position names are not consistent from theme to theme.');

        return $this->forward('Zikula\Bundle\CoreBundle\Controller\MainController::homeAction');
    }
}
