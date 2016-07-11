<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Controller;

use ModUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
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
     * @Template
     *
     * This is a standard function to modify the configuration parameters of the module.
     *
     * @param Request $request
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @return Response
     */
    public function configAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaGroupsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $variableApi = $this->get('zikula_extensions_module.api.variable');
        $modVars = $variableApi->getAll('ZikulaGroupsModule');
        $modVars['mailwarning'] = (bool)$modVars['mailwarning'];
        $modVars['hideclosed'] = (bool)$modVars['hideclosed'];

        // build a groups array suitable for the form choices
        $groupsList = [];
        $groups = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'getall');
        foreach ($groups as $group) {
            $groupsList[$group['gid']] = $group['name'];
        }

        $form = $this->createForm('Zikula\GroupsModule\Form\Type\ConfigType',
            $modVars, [
                'translator' => $this->get('translator.default'),
                'groups' => $groupsList
            ]
        );

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();

                // Update module variables.
                $itemsPerPage = isset($formData['itemsperpage']) ? $formData['itemsperpage'] : 25;
                $variableApi->set('ZikulaGroupsModule', 'itemsperpage', $itemsPerPage);

                $defaultGroup = isset($formData['defaultgroup']) ? $formData['defaultgroup'] : 1;
                $variableApi->set('ZikulaGroupsModule', 'defaultgroup', $defaultGroup);

                $mailWarning = isset($formData['mailwarning']) ? (bool)$formData['mailwarning'] : false;
                $variableApi->set('ZikulaGroupsModule', 'mailwarning', $mailWarning);

                $hideClosed = isset($formData['hideclosed']) ? (bool)$formData['hideclosed'] : false;
                $variableApi->set('ZikulaGroupsModule', 'hideclosed', $hideClosed);

                $this->addFlash('status', $this->__('Done! Module configuration updated.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulagroupsmodule_admin_view');
        }

        return [
            'form' => $form->createView()
        ];
    }
}
