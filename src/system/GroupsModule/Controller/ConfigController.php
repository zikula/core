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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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
     * @Template("ZikulaGroupsModule:Config:config.html.twig")
     *
     * This is a standard function to modify the configuration parameters of the module.
     *
     * @param Request $request
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @return array|RedirectResponse
     */
    public function configAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaGroupsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // build a groups array suitable for the form choices
        $groupsList = [];
        $groups = $this->get('zikula_groups_module.group_repository')->findAll();
        foreach ($groups as $group) {
            $groupsList[$group->getName()] = $group->getGid();
        }

        $form = $this->createForm('Zikula\GroupsModule\Form\Type\ConfigType', $this->getVars(), [
                'translator' => $this->get('translator.default'),
                'groups' => $groupsList
            ]
        );

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                $this->setVars($form->getData());
                $this->addFlash('status', $this->__('Done! Module configuration updated.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulagroupsmodule_group_adminlist');
        }

        return [
            'form' => $form->createView()
        ];
    }
}
