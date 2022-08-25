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

namespace Zikula\CategoriesBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DeletionType;
use Zikula\CategoriesBundle\Entity\CategoryRegistryEntity;
use Zikula\CategoriesBundle\Form\Type\CategoryRegistryType;
use Zikula\CategoriesBundle\Repository\CategoryRegistryRepositoryInterface;
use Zikula\ExtensionsBundle\Api\ApiInterface\CapabilityApiInterface;
use Zikula\ExtensionsBundle\Api\CapabilityApi;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
use Zikula\ThemeBundle\Engine\Annotation\Theme;

/**
 * @PermissionCheck("admin")
 */
#[Route('/categories/admin/registry')]
class RegistryController extends AbstractController
{
    /**
     * @Theme("admin")
     *
     * Creates or edits a category registry.
     */
    #[Route('/edit/{id}', name: 'zikulacategoriesbundle_registry_edit', requirements: ['id' => "^[1-9]\d*$"], defaults: ['id' => null])]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        CapabilityApiInterface $capabilityApi,
        CategoryRegistryRepositoryInterface $registryRepository,
        CategoryRegistryEntity $registryEntity = null
    ): Response {
        if (null === $registryEntity) {
            $registryEntity = new CategoryRegistryEntity();
        }

        $form = $this->createForm(CategoryRegistryType::class, $registryEntity, [
            'categorizableBundles' => $this->getCategorizableBundles($capabilityApi),
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

            return $this->redirectToRoute('zikulacategoriesbundle_registry_edit');
        }

        return $this->render('@ZikulaCategories/Registry/edit.html.twig', [
            'form' => $form->createView(),
            'registries' => $registryRepository->findAll(),
        ]);
    }

    private function getCategorizableBundles(CapabilityApiInterface $capabilityApi): array
    {
        $bundles = $capabilityApi->getExtensionsCapableOf(CapabilityApi::CATEGORIZABLE);
        $bundleOptions = [];
        foreach ($bundles as $bundle) {
            $bundleName = $bundle->getName();
            $bundleOptions[$bundleName] = $bundleName;
        }

        return $bundleOptions;
    }

    /**
     * @Theme("admin")
     *
     * Deletes a category registry.
     */
    #[Route('/delete/{id}', name: 'zikulacategoriesbundle_registry_delete', requirements: ['id' => "^[1-9]\d*$"])]
    public function delete(
        Request $request,
        EntityManagerInterface $entityManager,
        CategoryRegistryEntity $registry
    ): Response {
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

            return $this->redirectToRoute('zikulacategoriesbundle_registry_edit');
        }

        return $this->render('@ZikulaCategories/Registry/delete.html.twig', [
            'form' => $form->createView(),
            'registry' => $registry,
        ]);
    }
}
