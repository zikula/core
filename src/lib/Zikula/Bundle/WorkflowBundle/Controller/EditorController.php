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

        $workflowName = 'workflow.' . $request->query->get('workflow', '');
        if (!$this->get('service_container')->has($workflowName)) {
            throw new NotFoundHttpException($this->get('translator.default')->__f('Workflow "%workflow%" not found.', ['%workflow%' => $workflowName]));
        }

        $workflowDefinition = $this->get($workflowName)->getDefinition();

        return [
            'workflow' => $workflowDefinition
        ];
    }
}
