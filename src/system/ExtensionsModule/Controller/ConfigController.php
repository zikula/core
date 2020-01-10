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

namespace Zikula\ExtensionsModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Core\Controller\AbstractController;
use Zikula\ExtensionsModule\Helper\BundleSyncHelper;
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
     * @Template("@ZikulaExtensionsModule/Config/config.html.twig")
     *
     * @return array|Response
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions for the module
     */
    public function configAction(
        Request $request,
        BundleSyncHelper $bundleSyncHelper,
        CacheClearer $cacheClearer
    ) {
        if (!$this->hasPermission('ZikulaBlocksModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $form = $this->createFormBuilder($this->getVars())
            ->add('itemsperpage', IntegerType::class, [
                'label' => 'Items per page',
                'constraints' => [
                    new NotBlank(),
                    new GreaterThan(0)
                ]
            ])
            ->add('hardreset', CheckboxType::class, [
                'label' => 'Reset all extensions to default values',
                'label_attr' => ['class' => 'switch-custom'],
                'mapped' => false,
                'required' => false
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => 'Cancel',
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
                if (true === $form->get('hardreset')->getData()) {
                    $extensionsInFileSystem = $bundleSyncHelper->scanForBundles();
                    $bundleSyncHelper->syncExtensions($extensionsInFileSystem, true);
                    $cacheClearer->clear('symfony.routing');
                }
                $this->addFlash('status', $this->trans('Done! Module configuration updated.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->trans('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulaextensionsmodule_module_viewmodulelist');
        }

        return [
            'form' => $form->createView()
        ];
    }
}
