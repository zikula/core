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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DeletionType;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
use Zikula\ProfileBundle\Entity\PropertyEntity;
use Zikula\ProfileBundle\Form\Type\PropertyType;
use Zikula\ProfileBundle\Repository\PropertyRepositoryInterface;
use Zikula\ThemeBundle\Engine\Annotation\Theme;

#[Route('/profile/property')]
#[PermissionCheck('edit')]
class PropertyController extends AbstractController
{
    #[Route('/list', name: 'zikulaprofilebundle_property_listproperties')]
    #[Theme('admin')]
    public function listProperties(PropertyRepositoryInterface $propertyRepository): Response
    {
        return $this->render('@ZikulaProfile/Property/list.html.twig', [
            'properties' => $propertyRepository->findBy([], ['weight' => 'ASC']),
        ]);
    }

    #[Route('/edit/{id}', name: 'zikulaprofilebundle_property_edit', defaults: ['id' => null])]
    #[Theme('admin')]
    public function edit(Request $request, ManagerRegistry $doctrine, PropertyEntity $propertyEntity = null): Response
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
                $this->addFlash('success', 'Property saved.');
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('info', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulaprofilebundle_property_listproperties');
        }

        return $this->render('@ZikulaProfile/Property/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'zikulaprofilebundle_property_delete')]
    #[Theme('admin')]
    public function delete(Request $request, ManagerRegistry $doctrine, PropertyEntity $propertyEntity): Response
    {
        $form = $this->createForm(DeletionType::class, $propertyEntity);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $propertyEntity = $form->getData();
                $doctrine->getManager()->remove($propertyEntity);
                $doctrine->getManager()->flush();
                $this->addFlash('success', 'Property removed.');
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('info', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulaprofilebundle_property_listproperties');
        }

        return $this->render('@ZikulaProfile/Property/delete.html.twig', [
            'id' => $propertyEntity->getId(),
            'form' => $form->createView(),
        ]);
    }
}
