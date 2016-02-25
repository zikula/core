<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Component\SortableColumns\Column;
use Zikula\Component\SortableColumns\SortableColumns;
use Zikula\Core\Controller\AbstractController;
use Zikula\ExtensionsModule\Api\ExtensionApi;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\ExtensionEvents;
use Zikula\ExtensionsModule\Util as ExtensionsUtil;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class ModuleController
 * @package Zikula\ExtensionsModule\Controller
 * @Route("/module")
 */
class ModuleController extends AbstractController
{
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

        $sortableColumns = new SortableColumns($this->get('router'), 'zikulaextensionsmodule_module_viewmodulelist');
        $sortableColumns->addColumns([new Column('displayname'), new Column('state')]);
        $sortableColumns->setOrderByFromRequest($request);

        $upgradedExtensions = [];
        $vetoEvent = new GenericEvent();
        $this->get('event_dispatcher')->dispatch(ExtensionEvents::REGENERATE_VETO, $vetoEvent);
        if (!$vetoEvent->isPropagationStopped() && $pos == 1) {
            // regenerate the extension list only when viewing the first page
            $extensionsInFileSystem = $this->get('zikula_extensions_module.bundle_sync_helper')->scanForBundles();
            $upgradedExtensions = $this->get('zikula_extensions_module.bundle_sync_helper')->syncExtensions($extensionsInFileSystem);
        }

        $pagedResult = $this->getDoctrine()->getManager()
            ->getRepository('ZikulaExtensionsModule:ExtensionEntity')
            ->getPagedCollectionBy([], [$sortableColumns->getSortColumn()->getName() => $sortableColumns->getSortDirection()], $this->getVar('itemsperpage'), $pos);

        return [
            'sort' => $sortableColumns->generateSortableColumns(),
            'pager' => ['limit' => $this->getVar('itemsperpage'), 'count' => count($pagedResult)],
            'modules' => $pagedResult,
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
        $this->get('zikula_core.common.csrf_token_handler')->validate($csrftoken);

        $extension = $this->getDoctrine()->getManager()->find('ZikulaExtensionsModule:ExtensionEntity', $id);
        if ($extension->getState() == ExtensionApi::STATE_NOTALLOWED) {
            $this->addFlash('error', $this->__f('Error! Activation of module %s not allowed.', ['%s' => $extension->getName()]));
        } else {
            // Update state
            $this->get('zikula_extensions_module.extension_state_helper')->updateState($id, ExtensionApi::STATE_ACTIVE);
            $event = new GenericEvent(null, $extension->toArray());
            // @todo is this a legacy event? refactor to Constant
            $this->get('event_dispatcher')->dispatch('installer.module.activated', $event);
            $this->get('zikula.cache_clearer')->clear('symfony.routing');
            $this->addFlash('status', $this->__f('Done! Activated %s module.', ['%s' => $extension->getName()]));
        }

        return $this->redirect($this->get('router')->generate('zikulaextensionsmodule_module_viewmodulelist'));
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
        $this->get('zikula_core.common.csrf_token_handler')->validate($csrftoken);

        $extension = $this->getDoctrine()->getManager()->find('ZikulaExtensionsModule:ExtensionEntity', $id);
        if ($this->get('zikula_extensions_module.api.extension')->isCoreModule($extension->getName())) {
            $this->addFlash('error', $this->__f('Error! You cannot deactivate this module [%s]. It is a mandatory core module, and is needed by the system.', ['%s' => $extension->getName()]));
        } else {
            // Update state
            $this->get('zikula_extensions_module.extension_state_helper')->updateState($id, ExtensionApi::STATE_INACTIVE);
            $event = new GenericEvent(null, $extension->toArray());
            // @todo is this a legacy event? refactor to Constant
            $this->get('event_dispatcher')->dispatch('installer.module.deactivated', $event);
            $this->get('zikula.cache_clearer')->clear('symfony.routing');
            $this->addFlash('status', $this->__('Done! Deactivated module.'));
        }

        return $this->redirect($this->get('router')->generate('zikulaextensionsmodule_module_viewmodulelist'));
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

        if ($forceDefaults) {
            $bundle = $this->get('zikula_extensions_module.api.extension')->getModuleInstanceOrNull($extension->getName());
            if (isset($bundle)) {
                $metaData = $bundle->getMetaData()->getFilteredVersionInfoArray();
            } else {
                // @todo for BC only, remove at Core-2.0
                $metaData = ExtensionsUtil::getVersionMeta($extension->getName());
            }
            $extension->setName($metaData['name']);
            $extension->setUrl($metaData['url']);
            $extension->setDescription($metaData['description']);
        }

        $form = $this->createForm('Zikula\ExtensionsModule\Form\Type\ExtensionModifyType', $extension);

        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->get('defaults')->isClicked()) {
                $this->addFlash('info', $this->__('Default values reloaded. Save to confirm.'));

                return $this->redirect($this->generateUrl('zikulaextensionsmodule_module_modify', ['id' => $extension->getId(), 'forceDefaults' => 1]));
            }
            if ($form->get('save')->isClicked()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($extension);
                $em->flush();
                $this->addFlash('status', $this->__('Done! Extension updated.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirect($this->generateUrl('zikulaextensionsmodule_module_viewmodulelist'));
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
        if (empty($extension)) {
            throw new NotFoundHttpException($this->__('Error! No such module ID exists.'));
        }
        if (!$this->hasPermission('ZikulaExtensionsModule::', $extension->getName() . "::" . $extension->getId(), ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        return [
            'extension' => $extension
        ];
    }

    public function installAction()
    {
    }

    /**
     * @Route("/upgrade/{id}/{csrftoken}", requirements={"id" = "^[1-9]\d*$"})
     *
     * Upgrade a module
     *
     * @param ExtensionEntity $extension
     * @param string $csrftoken
     * @return RedirectResponse
     */
    public function upgradeAction(ExtensionEntity $extension, $csrftoken)
    {
        $this->get('zikula_core.common.csrf_token_handler')->validate($csrftoken);

        $result = $this->get('zikula_extensions_module.extension_helper')->upgrade($extension);
        if ($result) {
            $this->addFlash('status', $this->__f('%name% upgraded to new version and activated.', ['%name%' => $extension->getDisplayname()]));
        } else {
            $this->addFlash('error', $this->__('Extension upgrade failed!'));
        }

        return $this->redirect($this->generateUrl('zikulaextensionsmodule_module_viewmodulelist'));
    }

    public function uninstallAction()
    {
    }

    public function updateAllAction()
    {
        // unsure if this is wanted
    }
}
