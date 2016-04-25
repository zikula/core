<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\BlocksModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
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
     * @Template
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function configAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaBlocksModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $form = $this->createFormBuilder($this->getVars())
            ->add('collapseable', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $this->__('Enable block collapse icons'),
                'required' => false
            ])
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->__('Save'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ])
            ->getForm();

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                $this->setVars($form->getData());
                $this->addFlash('status', __('Done! Module configuration updated.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', __('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulablocksmodule_admin_view');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
