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

namespace Zikula\ProfileBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DeletionType;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
use Zikula\ProfileBundle\Entity\PropertyEntity;
use Zikula\ProfileBundle\Form\Type\PropertyType;
use Zikula\ProfileBundle\Repository\PropertyRepositoryInterface;
use Zikula\ThemeBundle\Engine\Annotation\Theme;

/**
 * @PermissionCheck("edit")
 */
#[Route('/profile/property')]
class PropertyController extends AbstractController
{
    /**
     * @Theme("admin")
     * @Template("@ZikulaProfile/Property/list.html.twig")
     */
    #[Route('/list', name: 'zikulaprofilebundle_property_listproperties')]
    public function listProperties(PropertyRepositoryInterface $propertyRepository): array
    {
        return [
            'properties' => $propertyRepository->findBy([], ['weight' => 'ASC']),
        ];
    }

    /**
     * @Theme("admin")
     * @Template("@ZikulaProfile/Property/edit.html.twig")
     *
     * @return array|RedirectResponse
     */
    #[Route('/edit/{id}', name: 'zikulaprofilebundle_property_edit', defaults: ['id' => null])]
    public function edit(Request $request, ManagerRegistry $doctrine, PropertyEntity $propertyEntity = null)
    {
        if (!isset($propertyEntity)) {
            $propertyEntity = new PropertyEntity();
        }
        $form = $this->createForm(PropertyType::class, $propertyEntity);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $propertyEntity = $form->getData();
                $doctrine->getManager()->persist($propertyEntity);
                $doctrine->getManager()->flush();
                $this->addFlash('success', $this->trans('Property saved.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('info', $this->trans('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulaprofilebundle_property_listproperties');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Theme("admin")
     * @Template("@ZikulaProfile/Property/delete.html.twig")
     *
     * @return array|RedirectResponse
     */
    #[Route('/delete/{id}', name: 'zikulaprofilebundle_property_delete')]
    public function delete(Request $request, ManagerRegistry $doctrine, PropertyEntity $propertyEntity)
    {
        $form = $this->createForm(DeletionType::class, $propertyEntity);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $propertyEntity = $form->getData();
                $doctrine->getManager()->remove($propertyEntity);
                $doctrine->getManager()->flush();
                $this->addFlash('success', $this->trans('Property removed.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('info', $this->trans('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulaprofilebundle_property_listproperties');
        }

        return [
            'id' => $propertyEntity->getId(),
            'form' => $form->createView()
        ];
    }
}
