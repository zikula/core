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

namespace Zikula\CategoriesModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DeletionType;
use Zikula\CategoriesModule\Builder\EntitySelectionBuilder;
use Zikula\CategoriesModule\Entity\CategoryRegistryEntity;
use Zikula\CategoriesModule\Entity\RepositoryInterface\CategoryRegistryRepositoryInterface;
use Zikula\CategoriesModule\Form\Type\CategoryRegistryType;
use Zikula\Core\Controller\AbstractController;
use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;
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
     * @Theme("admin")
     * @Template("ZikulaCategoriesModule:Registry:edit.html.twig")
     *
     * Creates or edits a category registry.
     *
     * @param Request $request
     * @param CapabilityApiInterface $capabilityApi
     * @param CategoryRegistryRepositoryInterface $registryRepository
     * @param CategoryRegistryEntity $registryEntity
     *
     * @return array|RedirectResponse
     */
    public function editAction(
        Request $request,
        CapabilityApiInterface $capabilityApi,
        CategoryRegistryRepositoryInterface $registryRepository,
        CategoryRegistryEntity $registryEntity = null
    ) {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        if (null === $registryEntity) {
            $registryEntity = new CategoryRegistryEntity();
        }

        $form = $this->createForm(CategoryRegistryType::class, $registryEntity, [
            'categorizableModules' => $this->getCategorizableModules($capabilityApi),
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
            'registries' => $registryRepository->findAll()
        ];
    }

    private function getCategorizableModules(CapabilityApiInterface $capabilityApi)
    {
        $modules = $capabilityApi->getExtensionsCapableOf(CapabilityApi::CATEGORIZABLE);
        $moduleOptions = [];
        foreach ($modules as $module) {
            $moduleOptions[$module->getName()] = $module->getName();
        }

        return $moduleOptions;
    }

    /**
     * @Route("/delete/{id}", requirements={"id" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template("ZikulaCategoriesModule:Registry:delete.html.twig")
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
        $form = $this->createForm(DeletionType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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
