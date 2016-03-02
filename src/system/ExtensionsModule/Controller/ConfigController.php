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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Zikula\Core\Controller\AbstractController;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class ConfigController
 * @Route("/config")
 */
class ConfigController extends AbstractController
{
    /**
     * @Route("")
     * @Theme("admin")
     * @Template
     *
     * Display services available to the module
     *
     * @return Response
     */
    public function configAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaBlocksModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $form = $this->createFormBuilder($this->getVars())
            ->add('itemsperpage', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', ['label' => $this->__('Items per page'),
                'constraints' => [
                    new NotBlank(),
                    new GreaterThan(0)
                ]
            ])
            ->add('hardreset', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $this->__('Reset all extensions to default values'),
                'mapped' => false,
                'required' => false
            ])
            ->add('save', 'submit', ['label' => $this->__('Save')])
            ->add('cancel', 'submit', ['label' => $this->__('Cancel')])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $this->setVars($form->getData());
                if ($form->get('hardreset')->getData() == true) {
                    $extensionsInFileSystem = $this->get('zikula_extensions_module.bundle_sync_helper')->scanForBundles();
                    $this->get('zikula_extensions_module.bundle_sync_helper')->syncExtensions($extensionsInFileSystem, true);
                    $this->get('zikula.cache_clearer')->clear('symfony.routing');
                }
                $this->addFlash('status', $this->__('Done! Module configuration updated.'));
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
}
