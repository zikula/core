<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DeletionType;
use Zikula\CategoriesModule\Entity\CategoryRegistryEntity;
use Zikula\CategoriesModule\Entity\RepositoryInterface\CategoryRegistryRepositoryInterface;
use Zikula\CategoriesModule\Form\Type\CategoryRegistryType;
use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;
use Zikula\ExtensionsModule\Api\CapabilityApi;
use Zikula\PermissionsModule\Annotation\PermissionCheck;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Controller for handling category registries.
 *
 * @Route("/registry")
 * @PermissionCheck("admin")
 */
class RegistryController extends AbstractController
{
    /**
     * @Route("/edit/{id}", requirements={"id" = "^[1-9]\d*$"}, defaults={"id" = null})
     * @Theme("admin")
     * @Template("@ZikulaCategoriesModule/Registry/edit.html.twig")
     *
     * Creates or edits a category registry.
     *
     * @return array|RedirectResponse
     */
    public function editAction(
        Request $request,
        EntityManagerInterface $entityManager,
        CapabilityApiInterface $capabilityApi,
        CategoryRegistryRepositoryInterface $registryRepository,
        CategoryRegistryEntity $registryEntity = null
    ) {
        if (null === $registryEntity) {
            $registryEntity = new CategoryRegistryEntity();
        }

        $form = $this->createForm(CategoryRegistryType::class, $registryEntity, [
            'categorizableModules' => $this->getCategorizableModules($capabilityApi)
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $entityManager->persist($registryEntity);
                $entityManager->flush();
                $this->addFlash('success', 'Done! Registry updated.');
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulacategoriesmodule_registry_edit');
        }

        return [
            'form' => $form->createView(),
            'registries' => $registryRepository->findAll()
        ];
    }

    private function getCategorizableModules(CapabilityApiInterface $capabilityApi): array
    {
        $modules = $capabilityApi->getExtensionsCapableOf(CapabilityApi::CATEGORIZABLE);
        $moduleOptions = [];
        foreach ($modules as $module) {
            $moduleName = $module->getName();
            $moduleOptions[$moduleName] = $moduleName;
        }

        return $moduleOptions;
    }

    /**
     * @Route("/delete/{id}", requirements={"id" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template("@ZikulaCategoriesModule/Registry/delete.html.twig")
     *
     * Deletes a category registry.
     *
     * @return array|RedirectResponse
     */
    public function deleteAction(
        Request $request,
        EntityManagerInterface $entityManager,
        CategoryRegistryEntity $registry
    ) {
        $form = $this->createForm(DeletionType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $entityManager->remove($registry);
                $entityManager->flush();
                $this->addFlash('success', 'Done! Registry entry deleted.');
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulacategoriesmodule_registry_edit');
        }

        return [
            'form' => $form->createView(),
            'registry' => $registry
        ];
    }
}
