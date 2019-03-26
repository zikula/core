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

namespace Zikula\Bundle\WorkflowBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Workflow\MarkingStore\MultipleStateMarkingStore;
use Symfony\Component\Workflow\MarkingStore\SingleStateMarkingStore;
use Symfony\Component\Workflow\Registry;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Workflow editor controller class.
 *
 * @Route("/editor")
 */
class EditorController extends Controller
{
    /**
     * This is the default action handling the index action.
     *
     * @Route("/index",
     *        methods = {"GET"}
     * )
     * @Theme("admin")
     * @Template("ZikulaWorkflowBundle:Editor:index.html.twig")
     *
     * @param Request $request
     * @param PermissionApiInterface $permissionApi
     * @param Registry $workflowRegistry
     * @param TranslatorInterface $translator
     *
     * @return Response Output
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     * @throws NotFoundHttpException Thrown if the desired workflow could not be found
     */
    public function indexAction(
        Request $request,
        PermissionApiInterface $permissionApi,
        Registry $workflowRegistry,
        TranslatorInterface $translator
    ) {
        if (!$permissionApi->hasPermission('ZikulaWorkflowBundle::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $workflowType = 'workflow';
        $workflowName = $workflowType . '.' . $request->query->get('workflow', '');
        if (!$this->get('service_container')->has($workflowName)) {
            $workflowType = 'state_machine';
            $workflowName = $workflowType . '.' . $request->query->get('workflow', '');
        }
        if (!$this->get('service_container')->has($workflowName)) {
            throw new NotFoundHttpException($translator->__f('Workflow "%workflow%" not found.', ['%workflow%' => $workflowName]));
        }

        $workflow = $this->get($workflowName);
        $workflowDefinition = $workflow->getDefinition();

        $markingStoreType = '';
        $markingStoreField = '';
        $supportedEntityClassNames = [];
        try {
            $markingStore = $workflow->getMarkingStore();
            if ($markingStore instanceof MultipleStateMarkingStore) {
                $markingStoreType = 'multiple_state';
            } elseif ($markingStore instanceof SingleStateMarkingStore) {
                $markingStoreType = 'single_state';
            }
            $markingStoreField = $markingStore->getProperty();

            $reflection = new \ReflectionClass(get_class($workflowRegistry));
            $workflowsProperty = $reflection->getProperty('workflows');
            $workflowsProperty->setAccessible(true);
            $workflows = $workflowsProperty->getValue($workflowRegistry);
            foreach ($workflows as list($aWorkflow, $workflowClass)) {
                if ($aWorkflow->getName() === $workflow->getName()) {
                    if (method_exists($workflowClass, 'getClassName')) {
                        $workflowClass = $workflowClass->getClassName();
                    }
                    $supportedEntityClassNames[] = $workflowClass;
                }
            }
        } catch (\ReflectionException $e) {
            $markingStoreType = 'single_state';
            $markingStoreField = 'state';
        }

        return [
            'name' => $workflow->getName(),
            'type' => $workflowType,
            'markingStoreType' => $markingStoreType,
            'markingStoreField' => $markingStoreField,
            'supportedEntities' => $supportedEntityClassNames,
            'workflow' => $workflowDefinition
        ];
    }
}
