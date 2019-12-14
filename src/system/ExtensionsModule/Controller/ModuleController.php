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

namespace Zikula\ExtensionsModule\Controller;

use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\BlocksModule\Entity\RepositoryInterface\BlockRepositoryInterface;
use Zikula\Bundle\CoreBundle\Bundle\MetaData;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DeletionType;
use Zikula\Component\SortableColumns\Column;
use Zikula\Component\SortableColumns\SortableColumns;
use Zikula\Core\AbstractBundle;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\CoreEvents;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Event\ModuleStateEvent;
use Zikula\ExtensionsModule\Constant;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
use Zikula\ExtensionsModule\ExtensionEvents;
use Zikula\ExtensionsModule\Form\Type\ExtensionInstallType;
use Zikula\ExtensionsModule\Form\Type\ExtensionModifyType;
use Zikula\ExtensionsModule\Helper\BundleSyncHelper;
use Zikula\ExtensionsModule\Helper\ExtensionDependencyHelper;
use Zikula\ExtensionsModule\Helper\ExtensionHelper;
use Zikula\ExtensionsModule\Helper\ExtensionStateHelper;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class ModuleController
 * @Route("/module")
 */
class ModuleController extends AbstractController
{
    private const NEW_ROUTES_AVAIL = 'new.routes.avail';

    /**
     * @Route("/list/{pos}")
     * @Theme("admin")
     * @Template("ZikulaExtensionsModule:Module:viewModuleList.html.twig")
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions for the module
     */
    public function viewModuleListAction(
        Request $request,
        ExtensionRepositoryInterface $extensionRepository,
        BundleSyncHelper $bundleSyncHelper,
        RouterInterface $router,
        int $pos = 1
    ): array {
        if (!$this->hasPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $modulesJustInstalled = $request->query->get('justinstalled');
        if (!empty($modulesJustInstalled)) {
            // notify the event dispatcher that new routes are available (ids of modules just installed avail as args)
            $event = new GenericEvent(null, json_decode($modulesJustInstalled));
            $this->get('event_dispatcher')->dispatch(self::NEW_ROUTES_AVAIL, $event);
        }

        $sortableColumns = new SortableColumns($router, 'zikulaextensionsmodule_module_viewmodulelist');
        $sortableColumns->addColumns([new Column('displayname'), new Column('state')]);
        $sortableColumns->setOrderByFromRequest($request);

        $upgradedExtensions = [];
        $vetoEvent = new GenericEvent();
        $this->get('event_dispatcher')->dispatch(ExtensionEvents::REGENERATE_VETO, $vetoEvent);
        if (1 === $pos && !$vetoEvent->isPropagationStopped()) {
            // regenerate the extension list only when viewing the first page
            $extensionsInFileSystem = $bundleSyncHelper->scanForBundles();
            $upgradedExtensions = $bundleSyncHelper->syncExtensions($extensionsInFileSystem);
        }

        $pagedResult = $extensionRepository->getPagedCollectionBy([], [
            $sortableColumns->getSortColumn()->getName() => $sortableColumns->getSortDirection()
        ], $this->getVar('itemsperpage'), $pos);

        $adminRoutes = [];

        foreach ($pagedResult as $module) {
            if (Constant::STATE_ACTIVE !== $module['state'] || !isset($module['capabilities']['admin']) || empty($module['capabilities']['admin'])) {
                continue;
            }

            $adminCapabilityInfo = $module['capabilities']['admin'];
            $adminUrl = '';
            if (isset($adminCapabilityInfo['route'])) {
                try {
                    $adminUrl = $router->generate($adminCapabilityInfo['route']);
                } catch (RouteNotFoundException $routeNotFoundException) {
                    // do nothing, just skip this link
                }
            } elseif (isset($adminCapabilityInfo['url'])) {
                $adminUrl = $adminCapabilityInfo['url'];
            }

            if (!empty($adminUrl)) {
                $adminRoutes[$module['name']] = $adminUrl;
            }
        }

        return [
            'sort' => $sortableColumns->generateSortableColumns(),
            'pager' => [
                'limit' => $this->getVar('itemsperpage'),
                'count' => count($pagedResult)
            ],
            'modules' => $pagedResult,
            'adminRoutes' => $adminRoutes,
            'upgradedExtensions' => $upgradedExtensions
        ];
    }

    /**
     * @Route("/modules/activate/{id}/{token}", methods = {"GET"}, requirements={"id" = "^[1-9]\d*$"})
     *
     * Activate an extension.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions for the module
     */
    public function activateAction(
        int $id,
        string $token,
        ExtensionRepositoryInterface $extensionRepository,
        ExtensionStateHelper $extensionStateHelper,
        CacheClearer $cacheClearer
    ): RedirectResponse {
        if (!$this->hasPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('activate-extension', $token)) {
            throw new AccessDeniedException();
        }

        /** @var ExtensionEntity $extension */
        $extension = $extensionRepository->find($id);
        if (Constant::STATE_NOTALLOWED === $extension->getState()) {
            $this->addFlash('error', $this->__f('Error! Activation of module %s not allowed.', ['%s' => $extension->getName()]));
        } else {
            // Update state
            $extensionStateHelper->updateState($id, Constant::STATE_ACTIVE);
            $cacheClearer->clear('symfony.routing');
            $this->addFlash('status', $this->__f('Done! Activated %s module.', ['%s' => $extension->getName()]));
        }

        return $this->redirectToRoute('zikulaextensionsmodule_module_viewmodulelist');
    }

    /**
     * @Route("/modules/deactivate/{id}/{token}", methods = {"GET"}, requirements={"id" = "^[1-9]\d*$"})
     *
     * Deactivate an extension
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions for the module
     */
    public function deactivateAction(
        int $id,
        string $token,
        ExtensionRepositoryInterface $extensionRepository,
        ExtensionStateHelper $extensionStateHelper,
        CacheClearer $cacheClearer
    ): RedirectResponse {
        if (!$this->hasPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('deactivate-extension', $token)) {
            throw new AccessDeniedException();
        }

        /** @var ExtensionEntity $extension */
        $extension = $extensionRepository->find($id);
        if (null !== $extension) {
            if (ZikulaKernel::isCoreModule($extension->getName())) {
                $this->addFlash('error', $this->__f('Error! You cannot deactivate this extension [%s]. It is a mandatory core extension, and is required by the system.', ['%s' => $extension->getName()]));
            } else {
                // Update state
                $extensionStateHelper->updateState($id, Constant::STATE_INACTIVE);
                $cacheClearer->clear('symfony.routing');
                $this->addFlash('status', $this->__('Done! Deactivated module.'));
            }
        }

        return $this->redirectToRoute('zikulaextensionsmodule_module_viewmodulelist');
    }

    /**
     * @Route("/modify/{id}/{forceDefaults}", requirements={"id" = "^[1-9]\d*$", "forceDefaults" = "0|1"})
     * @Theme("admin")
     * @Template("ZikulaExtensionsModule:Module:modify.html.twig")
     *
     * Modify a module.
     *
     * @return array|RedirectResponse
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions for modifying the extension
     */
    public function modifyAction(
        Request $request,
        ExtensionEntity $extension,
        CacheClearer $cacheClearer,
        bool $forceDefaults = false
    ) {
        if (!$this->hasPermission('ZikulaExtensionsModule::modify', $extension->getName() . '::' . $extension->getId(), ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        /** @var AbstractBundle $bundle */
        $bundle = $this->get('kernel')->getModule($extension->getName());
        $metaData = $bundle->getMetaData()->getFilteredVersionInfoArray();

        if ($forceDefaults) {
            $extension->setName($metaData['name']);
            $extension->setUrl($metaData['url']);
            $extension->setDescription($metaData['description']);
        }

        $form = $this->createForm(ExtensionModifyType::class, $extension);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('defaults')->isClicked()) {
                $this->addFlash('info', $this->__('Default values reloaded. Save to confirm.'));

                return $this->redirectToRoute('zikulaextensionsmodule_module_modify', ['id' => $extension->getId(), 'forceDefaults' => 1]);
            }
            if ($form->get('save')->isClicked()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($extension);
                $em->flush();

                $cacheClearer->clear('symfony.routing');
                $this->addFlash('status', $this->__('Done! Extension updated.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulaextensionsmodule_module_viewmodulelist');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/compatibility/{id}", methods = {"GET"}, requirements={"id" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template("ZikulaExtensionsModule:Module:compatibility.html.twig")
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
     * @Route("/install/{id}/{token}", requirements={"id" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template("ZikulaExtensionsModule:Module:install.html.twig")
     *
     * Install and initialise an extension.
     *
     * @return array|RedirectResponse
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission for the module
     */
    public function installAction(
        Request $request,
        ExtensionEntity $extension,
        string $token,
        ExtensionRepositoryInterface $extensionRepository,
        ExtensionHelper $extensionHelper,
        ExtensionStateHelper $extensionStateHelper,
        ExtensionDependencyHelper $dependencyHelper,
        CacheClearer $cacheClearer
    ) {
        if (!$this->hasPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $id = $extension->getId();
        if (!$this->isCsrfTokenValid('install-extension', $token)) {
            throw new AccessDeniedException();
        }

        if (!$this->get('kernel')->isBundle($extension->getName())) {
            $extensionStateHelper->updateState($id, Constant::STATE_TRANSITIONAL);
            $cacheClearer->clear('symfony');

            return $this->redirectToRoute('zikulaextensionsmodule_module_install', ['id' => $id]);
        }
        $unsatisfiedDependencies = $dependencyHelper->getUnsatisfiedExtensionDependencies($extension);
        $form = $this->createForm(ExtensionInstallType::class, [
            'dependencies' => $this->formatDependencyCheckboxArray($extensionRepository, $unsatisfiedDependencies)
        ]);
        $hasNoUnsatisfiedDependencies = empty($unsatisfiedDependencies);
        $form->handleRequest($request);
        if ($hasNoUnsatisfiedDependencies || ($form->isSubmitted() && $form->isValid())) {
            if ($hasNoUnsatisfiedDependencies || $form->get('install')->isClicked()) {
                $extensionsInstalled = [];
                $data = $form->getData();
                foreach ($data['dependencies'] as $dependencyId => $installSelected) {
                    if (!$installSelected && MetaData::DEPENDENCY_REQUIRED !== $unsatisfiedDependencies[$dependencyId]->getStatus()) {
                        continue;
                    }
                    $dependencyExtensionEntity = $extensionRepository->get($unsatisfiedDependencies[$dependencyId]->getModname());
                    if (isset($dependencyExtensionEntity)) {
                        if (!$extensionHelper->install($dependencyExtensionEntity)) {
                            $this->addFlash('error', $this->__f('Failed to install dependency %s!', ['%s' => $dependencyExtensionEntity->getName()]));

                            return $this->redirectToRoute('zikulaextensionsmodule_module_viewmodulelist');
                        }
                        $extensionsInstalled[] = $dependencyExtensionEntity->getId();
                        $this->addFlash('status', $this->__f('Installed dependency %s.', ['%s' => $dependencyExtensionEntity->getName()]));
                    } else {
                        $this->addFlash('warning', $this->__f('Warning: could not install selected dependency %s', ['%s' => $unsatisfiedDependencies[$dependencyId]->getModname()]));
                    }
                }
                if ($extensionHelper->install($extension)) {
                    $this->addFlash('status', $this->__f('Done! Installed %s.', ['%s' => $extension->getName()]));
                    $extensionsInstalled[] = $id;
                    $cacheClearer->clear('symfony');

                    return $this->redirectToRoute('zikulaextensionsmodule_module_postinstall', ['extensions' => json_encode($extensionsInstalled)]);
                }
                $extensionStateHelper->updateState($id, Constant::STATE_UNINITIALISED);
                $this->addFlash('error', $this->__f('Initialization of %s failed!', ['%s' => $extension->getName()]));
            }
            if ($form->get('cancel')->isClicked()) {
                $extensionStateHelper->updateState($id, Constant::STATE_UNINITIALISED);
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulaextensionsmodule_module_viewmodulelist');
        }

        return [
            'dependencies' => $unsatisfiedDependencies,
            'extension' => $extension,
            'form' => $form->createView()
        ];
    }

    /**
     * Post-installation action to trigger the MODULE_POSTINSTALL event.
     * The additional Action is required because this event must occur AFTER the rebuild of the cache which occurs on Request.
     *
     * @Route("/postinstall/{extensions}", methods = {"GET"})
     */
    public function postInstallAction(
        ExtensionRepositoryInterface $extensionRepository,
        ZikulaHttpKernelInterface $kernel,
        EventDispatcherInterface $eventDispatcher,
        string $extensions = null
    ): RedirectResponse {
        if (!empty($extensions)) {
            $extensions = json_decode($extensions);
            foreach ($extensions as $extensionId) {
                /** @var ExtensionEntity $extensionEntity */
                $extensionEntity = $extensionRepository->find($extensionId);
                if (null === $extensionRepository) {
                    continue;
                }
                $bundle = $kernel->getModule($extensionEntity->getName());
                if (null === $bundle) {
                    continue;
                }
                $event = new ModuleStateEvent($bundle, $extensionEntity->toArray());
                $eventDispatcher->dispatch($event, CoreEvents::MODULE_POSTINSTALL);
            }
            // currently commented out because it takes a long time.
            //$extensionHelper->installAssets();
        }

        return $this->redirectToRoute('zikulaextensionsmodule_module_viewmodulelist', ['justinstalled' => json_encode($extensions)]);
    }

    /**
     * Create array suitable for checkbox FormType [[ID => bool][ID => bool]].
     */
    private function formatDependencyCheckboxArray(
        ExtensionRepositoryInterface $extensionRepository,
        array $dependencies
    ): array {
        $return = [];
        foreach ($dependencies as $dependency) {
            /** @var ExtensionEntity $dependencyExtension */
            $dependencyExtension = $extensionRepository->get($dependency->getModname());
            $return[$dependency->getId()] = null !== $dependencyExtension;
        }

        return $return;
    }

    /**
     * @Route("/upgrade/{id}/{token}", requirements={"id" = "^[1-9]\d*$"})
     *
     * Upgrade an extension.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission for the module
     */
    public function upgradeAction(
        ExtensionEntity $extension,
        $token,
        ExtensionHelper $extensionHelper
    ): RedirectResponse {
        if (!$this->hasPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('upgrade-extension', $token)) {
            throw new AccessDeniedException();
        }

        $result = $extensionHelper->upgrade($extension);
        if ($result) {
            $this->addFlash('status', $this->__f('%name% upgraded to new version and activated.', ['%name%' => $extension->getDisplayname()]));
        } else {
            $this->addFlash('error', $this->__('Extension upgrade failed!'));
        }

        return $this->redirectToRoute('zikulaextensionsmodule_module_viewmodulelist');
    }

    /**
     * @Route("/uninstall/{id}/{token}", requirements={"id" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template("ZikulaExtensionsModule:Module:uninstall.html.twig")
     *
     * Uninstall an extension.
     *
     * @return array|Response|RedirectResponse
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission for the module
     */
    public function uninstallAction(
        Request $request,
        ExtensionEntity $extension,
        string $token,
        BlockRepositoryInterface $blockRepository,
        ExtensionHelper $extensionHelper,
        ExtensionStateHelper $extensionStateHelper,
        ExtensionDependencyHelper $dependencyHelper,
        CacheClearer $cacheClearer
    ) {
        if (!$this->hasPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('uninstall-extension', $token)) {
            throw new AccessDeniedException();
        }

        if (Constant::STATE_MISSING === $extension->getState()) {
            throw new RuntimeException($this->__('Error! The requested extension cannot be uninstalled because its files are missing!'));
        }
        if (!$this->get('kernel')->isBundle($extension->getName())) {
            $extensionStateHelper->updateState($extension->getId(), Constant::STATE_TRANSITIONAL);
            $cacheClearer->clear('symfony');
        }
        $requiredDependents = $dependencyHelper->getDependentExtensions($extension);
        $blocks = $blockRepository->findBy(['module' => $extension]);

        $form = $this->createForm(DeletionType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                // remove dependent extensions
                if (!$extensionHelper->uninstallArray($requiredDependents)) {
                    $this->addFlash('error', $this->__('Error: Could not uninstall dependent extensions.'));

                    return $this->redirectToRoute('zikulaextensionsmodule_module_viewmodulelist');
                }
                // remove blocks
                foreach ($blocks as $block) {
                    $this->getDoctrine()->getManager()->remove($block);
                }
                $this->getDoctrine()->getManager()->flush();

                // remove the extension
                if ($extensionHelper->uninstall($extension)) {
                    $this->addFlash('status', $this->__('Done! Uninstalled extension.'));
                } else {
                    $this->addFlash('error', $this->__('Extension removal failed! (note: blocks and dependents may have been removed)'));
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulaextensionsmodule_module_viewmodulelist');
        }

        return [
            'form' => $form->createView(),
            'extension' => $extension,
            'blocks' => $blocks,
            'requiredDependents' => $requiredDependents
        ];
    }
}
