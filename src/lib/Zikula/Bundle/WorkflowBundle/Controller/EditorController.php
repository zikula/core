<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\WorkflowBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
     * @Template
     *
     * @param Request $request Current request instance
     *
     * @return Response Output
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     * @throws NotFoundHttpException Thrown if the desired workflow could not be found
     */
    public function indexAction(Request $request)
    {
        if (!$this->get('zikula_permissions_module.api.permission')->hasPermission('ZikulaWorkflowBundle::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $workflowType = 'workflow';
        $workflowName = $workflowType . '.' . $request->query->get('workflow', '');
        if (!$this->get('service_container')->has($workflowName)) {
            $workflowType = 'state_machine';
            $workflowName = $workflowType . '.' . $request->query->get('workflow', '');
        }
        if (!$this->get('service_container')->has($workflowName)) {
            throw new NotFoundHttpException($this->get('translator.default')->__f('Workflow "%workflow%" not found.', ['%workflow%' => $workflowName]));
        }

        $workflow = $this->get($workflowName);
        $workflowDefinition = $workflow->getDefinition();

        $markingStoreType = '';
        $markingStoreField = '';
        try {
            $reflection = new \ReflectionClass('Symfony\Component\Workflow\Workflow');
            $markingStoreProperty = $reflection->getProperty('markingStore');
            $markingStoreProperty->setAccessible(true);
            $markingStore = $markingStoreProperty->getValue($workflow);
            if ($markingStore instanceof Symfony\Component\Workflow\MarkingStore\MultipleStateMarkingStore) {
                $markingStoreType = 'multiple_state';
            } elseif ($markingStore instanceof Symfony\Component\Workflow\MarkingStore\SingleStateMarkingStore) {
                $markingStoreType = 'single_state';
            }

            $reflection = new \ReflectionClass(get_class($markingStore));
            $markingStoreFieldProperty = $reflection->getProperty('property');
            $markingStoreFieldProperty->setAccessible(true);
            $markingStoreField = $markingStoreFieldProperty->getValue($markingStore);
        } catch (\ReflectionException $e) {
            $markingStoreType = 'single_state';
            $markingStoreField = 'state';
        }

        return [
            'name' => $workflow->getName(),
            'type' => $workflowType,
            'markingStoreType' => $markingStoreType,
            'markingStoreField' => $markingStoreField,
            'workflow' => $workflowDefinition
        ];
    }
}
