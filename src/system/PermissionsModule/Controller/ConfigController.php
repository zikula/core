<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PermissionsModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\PermissionsModule\Form\Type\ConfigType;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class ConfigController
 * @Route("/config")
 */
class ConfigController extends AbstractController
{
    /**
     * @Route("/config")
     * @Theme("admin")
     * @Template("ZikulaPermissionsModule:Config:config.html.twig")
     *
     * @param Request $request
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @return Response
     */
    public function configAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $variableApi = $this->get('zikula_extensions_module.api.variable');
        $modVars = $variableApi->getAll('ZikulaPermissionsModule');
        $modVars['lockadmin'] = (bool)$modVars['lockadmin'];
        $modVars['filter'] = (bool)$modVars['filter'];

        $form = $this->createForm(ConfigType::class,
            $modVars, [
                'translator' => $this->get('translator.default')
            ]
        );

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();

                $error = false;

                $lockadmin = isset($formData['lockadmin']) ? (bool)$formData['lockadmin'] : false;
                $variableApi->set('ZikulaPermissionsModule', 'lockadmin', $lockadmin);

                $adminId = isset($formData['adminid']) ? (int)$formData['adminid'] : 1;
                if ($adminId != 0) {
                    $perm = $this->get('doctrine')->getRepository('ZikulaPermissionsModule:PermissionEntity')->find($adminId);
                    if (!$perm) {
                        $adminId = 0;
                        $error = true;
                    }
                }
                $variableApi->set('ZikulaPermissionsModule', 'adminid', $adminId);

                $filter = isset($formData['filter']) ? (bool)$formData['filter'] : false;
                $variableApi->set('ZikulaPermissionsModule', 'filter', $filter);

                $rowView = isset($formData['rowview']) ? (int)$formData['rowview'] : 25;
                $variableApi->set('ZikulaPermissionsModule', 'rowview', $rowView);

                $rowEdit = isset($formData['rowedit']) ? (int)$formData['rowedit'] : 35;
                $variableApi->set('ZikulaPermissionsModule', 'rowedit', $rowEdit);

                if (true === $error) {
                    $this->addFlash('error', $this->__('Error! Could not save configuration: unknown permission rule ID.'));
                } else {
                    $this->addFlash('status', $this->__('Done! Module configuration updated.'));
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulapermissionsmodule_permission_list');
        }

        return [
            'form' => $form->createView()
        ];
    }
}
