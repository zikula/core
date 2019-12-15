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

namespace Zikula\BlocksModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
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
     * @Template("@ZikulaBlocksModule/Config/config.html.twig")
     *
     * @return array|RedirectResponse
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions for the module
     */
    public function configAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaBlocksModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $form = $this->createFormBuilder($this->getVars())
            ->add('collapseable', CheckboxType::class, [
                'label' => $this->__('Enable block collapse icons'),
                'required' => false
            ])
            ->add('save', SubmitType::class, [
                'label' => $this->__('Save'),
                'icon' => 'fa-check',
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
            ->getForm()
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $this->setVars($form->getData());
                $this->addFlash('status', $this->__('Done! Module configuration updated.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulablocksmodule_admin_view');
        }

        return [
            'form' => $form->createView()
        ];
    }
}
