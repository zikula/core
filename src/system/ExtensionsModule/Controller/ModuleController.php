<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\Bundle\MetaData;
use Zikula\Component\SortableColumns\Column;
use Zikula\Component\SortableColumns\SortableColumns;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\CoreEvents;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Event\ModuleStateEvent;
use Zikula\ExtensionsModule\Api\ExtensionApi;
use Zikula\ExtensionsModule\Entity\ExtensionDependencyEntity;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\ExtensionEvents;
use Zikula\ExtensionsModule\Form\Type\ExtensionInstallType;
use Zikula\ExtensionsModule\Form\Type\ExtensionModifyType;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class ModuleController
 * @Route("/module")
 */
class ModuleController extends AbstractController
{
    const NEW_ROUTES_AVAIL = 'new.routes.avail';

    /**
     * @Route("/list/{pos}")
     * @Theme("admin")
     * @Template
     * @param Request $request
     * @param int $pos
     * @return array
     */
    public function viewModuleListAction(Request $request, $pos = 1)
    {
        if (!$this->hasPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $modulesJustInstalled = $request->query->get('justinstalled', null);
        if (!empty($modulesJustInstalled)) {
            // notify the event dispatcher that new routes are available (ids of modules just installed avail as args)
            $event = new GenericEvent(null, json_decode($modulesJustInstalled));
            $this->get('event_dispatcher')->dispatch(self::NEW_ROUTES_AVAIL, $event);
        }

        $sortableColumns = new SortableColumns($this->get('router'), 'zikulaextensionsmodule_module_viewmodulelist');
        $sortableColumns->addColumns([new Column('displayname'), new Column('state')]);
        $sortableColumns->setOrderByFromRequest($request);

        $upgradedExtensions = [];
        $vetoEvent = new GenericEvent();
        $this->get('event_dispatcher')->dispatch(ExtensionEvents::REGENERATE_VETO, $vetoEvent);
        if (!$vetoEvent->isPropagationStopped() && $pos == 1) {
            // regenerate the extension list only when viewing the first page
            $bundleSyncHelper = $this->get('zikula_extensions_module.bundle_sync_helper');
            $extensionsInFileSystem = $bundleSyncHelper->scanForBundles();
            $upgradedExtensions = $bundleSyncHelper->syncExtensions($extensionsInFileSystem);
        }

        $pagedResult = $this->getDoctrine()->getManager()
            ->getRepository('ZikulaExtensionsModule:ExtensionEntity')
            ->getPagedCollectionBy([], [$sortableColumns->getSortColumn()->getName() => $sortableColumns->getSortDirection()], $this->getVar('itemsperpage'), $pos);

        $adminRoutes = [];

        foreach ($pagedResult as $module) {
            if (!isset($module['capabilities']['admin']) || empty($module['capabilities']['admin']) || $module['state'] != ExtensionApi::STATE_ACTIVE) {
                continue;
            }

            $adminCapabilityInfo = $module['capabilities']['admin'];
            $adminUrl = '';
            if (isset($adminCapabilityInfo['route'])) {
                try {
                    $adminUrl = $this->get('router')->generate($adminCapabilityInfo['route']);
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
     * @Route("/modules/activate/{id}/{csrftoken}", requirements={"id" = "^[1-9]\d*$"})
     * @Method("GET")
     *
     * Activate an extension
     *
     * @param integer $id
     * @param string $csrftoken
     * @return RedirectResponse
     */
    public function activateAction($id, $csrftoken)
    {
        if (!$this->hasPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $this->get('zikula_core.common.csrf_token_handler')->validate($csrftoken);

        $extension = $this->getDoctrine()->getManager()->find('ZikulaExtensionsModule:ExtensionEntity', $id);
        if ($extension->getState() == ExtensionApi::STATE_NOTALLOWED) {
            $this->addFlash('error', $this->__f('Error! Activation of module %s not allowed.', ['%s' => $extension->getName()]));
        } else {
            // Update state
            $this->get('zikula_extensions_module.extension_state_helper')->updateState($id, ExtensionApi::STATE_ACTIVE);
            $this->get('zikula.cache_clearer')->clear('symfony.routing');
            $this->addFlash('status', $this->__f('Done! Activated %s module.', ['%s' => $extension->getName()]));
        }

        return $this->redirectToRoute('zikulaextensionsmodule_module_viewmodulelist');
    }

    /**
     * @Route("/modules/deactivate/{id}/{csrftoken}", requirements={"id" = "^[1-9]\d*$"})
     * @Method("GET")
     *
     * Deactivate an extension
     *
     * @param integer $id
     * @param string $csrftoken
     * @return RedirectResponse
     */
    public function deactivateAction($id, $csrftoken)
    {
        if (!$this->hasPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $this->get('zikula_core.common.csrf_token_handler')->validate($csrftoken);

        $extension = $this->getDoctrine()->getManager()->find('ZikulaExtensionsModule:ExtensionEntity', $id);
        if ($extension->getName() != 'ZikulaPageLockModule' && \ZikulaKernel::isCoreModule($extension->getName())) {
            $this->addFlash('error', $this->__f('Error! You cannot deactivate this extension [%s]. It is a mandatory core extension, and is required by the system.', ['%s' => $extension->getName()]));
        } else {
            // Update state
            $this->get('zikula_extensions_module.extension_state_helper')->updateState($id, ExtensionApi::STATE_INACTIVE);
            $this->get('zikula.cache_clearer')->clear('symfony.routing');
            $this->addFlash('status', $this->__('Done! Deactivated module.'));
        }

        return $this->redirectToRoute('zikulaextensionsmodule_module_viewmodulelist');
    }

    /**
     * @Route("/modify/{id}/{forceDefaults}", requirements={"id" = "^[1-9]\d*$", "forceDefaults" = "0|1"})
     * @Template
     * @Theme("admin")
     *
     * Modify a module.
     *
     * @param Request $request
     * @param ExtensionEntity $extension
     * @param bool $forceDefaults
     * @return RedirectResponse|Response
     */
    public function modifyAction(Request $request, ExtensionEntity $extension, $forceDefaults = false)
    {
        if (!$this->hasPermission('ZikulaExtensionsModule::modify', $extension->getName() . '::' . $extension->getId(), ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $bundle = $this->get('kernel')->getModule($extension->getName());
        $metaData = $bundle->getMetaData()->getFilteredVersionInfoArray();

        if ($forceDefaults) {
            $extension->setName($metaData['name']);
            $extension->setUrl($metaData['url']);
            $extension->setDescription($metaData['description']);
        }

        $form = $this->createForm(ExtensionModifyType::class, $extension, [
            'translator' => $this->get('translator.default')
        ]);

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('defaults')->isClicked()) {
                $this->addFlash('info', $this->__('Default values reloaded. Save to confirm.'));

                return $this->redirectToRoute('zikulaextensionsmodule_module_modify', ['id' => $extension->getId(), 'forceDefaults' => 1]);
            }
            if ($form->get('save')->isClicked()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($extension);
                $em->flush();

                $this->get('zikula.cache_clearer')->clear('symfony.routing');
                $this->addFlash('status', $this->__('Done! Extension updated.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulaextensionsmodule_module_viewmodulelist');
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/compatibility/{id}", requirements={"id" = "^[1-9]\d*$"})
     * @Method("GET")
     * @Template
     * @Theme("admin")
     *
     * Display information of a module compatibility with the version of the core
     *
     * @param ExtensionEntity $extension
     * @return Response symfony response object
     * @throws NotFoundHttpException Thrown if the requested module id doesn't exist
     * @throws AccessDeniedException Thrown if the user doesn't have admin permission to the requested module
     */
    public function compatibilityAction(ExtensionEntity $extension)
    {
        if (!$this->hasPermission('ZikulaExtensionsModule::', $extension->getName() . "::" . $extension->getId(), ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        return [
            'extension' => $extension
        ];
    }

    /**
     * @Route("/install/{id}", requirements={"id" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template
     *
     * Initialise an extension.
     *
     * @param ExtensionEntity $extension
     * @return RedirectResponse
     */
    public function installAction(Request $request, ExtensionEntity $extension)
    {
        $unsatisfiedDependencies = $this->get('zikula_extensions_module.extension_dependency_helper')->getUnsatisfiedExtensionDependencies($extension);
        $form = $this->createForm(ExtensionInstallType::class, [
            'dependencies' => $this->formatDependencyCheckboxArray($unsatisfiedDependencies)
        ], [
            'translator' => $this->get('translator.default')
        ]);

        if ($form->handleRequest($request)->isValid() || empty($unsatisfiedDependencies)) {
            if ($form->get('install')->isClicked() || empty($unsatisfiedDependencies)) {
                $extensionsInstalled = [];
                $data = $form->getData();
                foreach ($data['dependencies'] as $dependencyId => $installSelected) {
                    if (!$installSelected && $unsatisfiedDependencies[$dependencyId]->getStatus() != MetaData::DEPENDENCY_REQUIRED) {
                        continue;
                    }
                    $dependencyExtensionEntity = $this->get('zikula_extensions_module.extension_repository')->get($unsatisfiedDependencies[$dependencyId]->getModname());
                    if (isset($dependencyExtensionEntity)) {
                        if (!$this->get('zikula_extensions_module.extension_helper')->enableExtension($dependencyExtensionEntity)) {
                            $this->addFlash('error', $this->__f('Failed to install dependency %s!', ['%s' => $dependencyExtensionEntity->getName()]));

                            return $this->redirectToRoute('zikulaextensionsmodule_module_viewmodulelist');
                        }
                        $extensionsInstalled[] = $dependencyExtensionEntity->getId();
                        $this->addFlash('status', $this->__f('Installed dependency %s.', ['%s' => $dependencyExtensionEntity->getName()]));
                    } else {
                        $this->addFlash('warning', $this->__f('Warning: could not install selected dependency %s', ['%s' => $unsatisfiedDependencies[$dependencyId]->getModname()]));
                    }
                }
                if ($this->get('zikula_extensions_module.extension_helper')->install($extension)) {
                    $this->addFlash('status', $this->__f('Done! Installed %s.', ['%s' => $extension->getName()]));
                    $extensionsInstalled[] = $extension->getId();
                    $this->get('zikula.cache_clearer')->clear('symfony');

                    return $this->redirectToRoute('zikulaextensionsmodule_module_postinstall', ['extensions' => json_encode($extensionsInstalled)]);
                } else {
                    $this->addFlash('error', $this->__f('Initialization of %s failed!', ['%s' => $extension->getName()]));
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulaextensionsmodule_module_viewmodulelist');
        }

        return [
            'dependencies' => $unsatisfiedDependencies,
            'extension' => $extension,
            'form' => $form->createView(),
        ];
    }

    /**
     * Post-installation action to trigger the MODULE_POSTINSTALL event.
     * The additional Action is required because this event must occur AFTER the rebuild of the cache which occurs on Request.
     * @Route("/postinstall/{extensions}")
     * @Method("GET")
     * @param string $extensions
     * @return RedirectResponse
     */
    public function postInstallAction($extensions = null)
    {
        if (!empty($extensions)) {
            $extensions = json_decode($extensions);
            foreach ($extensions as $extensionId) {
                $extensionEntity = $this->get('zikula_extensions_module.extension_repository')->find($extensionId);
                $bundle = $this->get('kernel')->getModule($extensionEntity->getName());
                if (!empty($bundle)) {
                    $event = new ModuleStateEvent($bundle);
                    $this->get('event_dispatcher')->dispatch(CoreEvents::MODULE_POSTINSTALL, $event);
                }
            }
            // currently commented out because it takes a long time.
//            $this->get('zikula_extensions_module.extension_helper')->installAssets();
        }

        return $this->redirectToRoute('zikulaextensionsmodule_module_viewmodulelist', ['justinstalled' => json_encode($extensions)]);
    }

    /**
     * Create array suitable for checkbox FormType [[ID => bool][ID => bool]]
     * @param array $dependencies
     * @return array
     */
    private function formatDependencyCheckboxArray(array $dependencies)
    {
        $return = [];
        /** @var ExtensionDependencyEntity[] $dependencies */
        foreach ($dependencies as $dependency) {
            $dependencyExtension = $this->get('zikula_extensions_module.extension_repository')->get($dependency->getModname());
            $return[$dependency->getId()] = empty($dependencyExtension) ? false : true;
        }

        return $return;
    }

    /**
     * @Route("/upgrade/{id}/{csrftoken}", requirements={"id" = "^[1-9]\d*$"})
     *
     * Upgrade an extension.
     *
     * @param ExtensionEntity $extension
     * @param string $csrftoken
     * @return RedirectResponse
     */
    public function upgradeAction(ExtensionEntity $extension, $csrftoken)
    {
        if (!$this->hasPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $this->get('zikula_core.common.csrf_token_handler')->validate($csrftoken);

        $result = $this->get('zikula_extensions_module.extension_helper')->upgrade($extension);
        if ($result) {
            $this->addFlash('status', $this->__f('%name% upgraded to new version and activated.', ['%name%' => $extension->getDisplayname()]));
        } else {
            $this->addFlash('error', $this->__('Extension upgrade failed!'));
        }

        return $this->redirectToRoute('zikulaextensionsmodule_module_viewmodulelist');
    }

    /**
     * @Route("/uninstall/{id}", requirements={"id" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template
     *
     * Uninstall an extension.
     *
     * @param Request $request
     * @param ExtensionEntity $extension
     *
     * @return Response|RedirectResponse
     */
    public function uninstallAction(Request $request, ExtensionEntity $extension)
    {
        if (!$this->hasPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        if ($extension->getState() == ExtensionApi::STATE_MISSING) {
            throw new \RuntimeException($this->__("Error! The requested extension cannot be uninstalled because its files are missing!"));
        }
        $requiredDependents = $this->get('zikula_extensions_module.extension_dependency_helper')->getDependentExtensions($extension);
        $blocks = $this->getDoctrine()->getManager()->getRepository('ZikulaBlocksModule:BlockEntity')->findBy(['module' => $extension]);

        $form = $this->createFormBuilder()
            ->add('uninstall', SubmitType::class, [
                'label' => $this->__('Delete'),
                'icon' => 'fa-trash-o',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $this->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ])
            ->getForm();

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('uninstall')->isClicked()) {
                // remove dependent extensions
                if (!$this->get('zikula_extensions_module.extension_helper')->uninstallArray($requiredDependents)) {
                    $this->addFlash('error', $this->__('Error: Could not uninstall dependent extensions.'));

                    return $this->redirectToRoute('zikulaextensionsmodule_module_viewmodulelist');
                }
                // remove blocks
                foreach ($blocks as $block) {
                    $this->getDoctrine()->getManager()->remove($block);
                }
                $this->getDoctrine()->getManager()->flush();

                // remove the extension
                if ($this->get('zikula_extensions_module.extension_helper')->uninstall($extension)) {
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
