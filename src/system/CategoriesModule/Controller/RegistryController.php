<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\CategoriesModule\Builder\EntitySelectionBuilder;
use Zikula\CategoriesModule\Entity\CategoryRegistryEntity;
use Zikula\Core\Controller\AbstractController;
use Zikula\ExtensionsModule\Api\CapabilityApi;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * @Route("/registry")
 *
 * Controller for handling category registries.
 */
class RegistryController extends AbstractController
{
    /**
     * @Route("/edit/{id}", requirements={"id" = "^[1-9]\d*$"}, defaults={"id" = null})
     * @Template
     * @Theme("admin")
     *
     * Creates or edits a category registry.
     *
     * @param Request $request
     * @param CategoryRegistryEntity $registryEntity
     * @return array|RedirectResponse
     */
    public function editAction(Request $request, CategoryRegistryEntity $registryEntity = null)
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        if (null == $registryEntity) {
            $registryEntity = new CategoryRegistryEntity();
        }

        $form = $this->createForm('Zikula\CategoriesModule\Form\Type\CategoryRegistryType', $registryEntity, [
            'translator' => $this->getTranslator(),
            'categorizableModules' => $this->getCategorizableModules(),
            'entitySelectionBuilder' => new EntitySelectionBuilder($this->get('kernel'))
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $manager = $this->get('doctrine')->getManager();
                $manager->persist($registryEntity);
                $manager->flush();
                $this->addFlash('success', $this->__('Registry updated'));
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulacategoriesmodule_registry_edit');
        }

        return [
            'form' => $form->createView(),
            'registries' => $this->get('zikula_categories_module.category_registry_repository')->findAll()
        ];
    }

    private function getCategorizableModules()
    {
        $modules = $this->get('zikula_extensions_module.api.capability')->getExtensionsCapableOf(CapabilityApi::CATEGORIZABLE);
        $moduleOptions = [];
        foreach ($modules as $module) {
            $moduleOptions[$module->getName()] = $module->getName();
        }

        return $moduleOptions;
    }

    /**
     * @Route("/delete/{id}", requirements={"id" = "^[1-9]\d*$"})
     * @Template
     * @Theme("admin")
     *
     * Deletes a category registry.
     *
     * @param Request $request
     * @param CategoryRegistryEntity $registry
     * @return array|RedirectResponse
     */
    public function deleteAction(Request $request, CategoryRegistryEntity $registry)
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $form = $this->createForm('Zikula\Bundle\FormExtensionBundle\Form\Type\DeletionType');

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $entityManager = $this->get('doctrine')->getManager();
                $entityManager->remove($registry);
                $entityManager->flush();
                $this->addFlash('success', $this->__('Done! Registry entry deleted.'));
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulacategoriesmodule_registry_edit');
        }

        return [
            'form' => $form->createView(),
            'registry' => $registry
        ];
    }
}
