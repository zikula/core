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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Zikula\FormExtensionBundle\Form\Type\DeletionType;
use Zikula\ProfileBundle\Entity\Property;
use Zikula\ProfileBundle\Form\Type\PropertyType;
use Zikula\ProfileBundle\Repository\PropertyRepositoryInterface;

#[Route('/profile/property')]
#[IsGranted('ROLE_EDITOR')]
class PropertyController extends AbstractController
{
    #[Route('/list', name: 'zikulaprofilebundle_property_listproperties')]
    public function listProperties(PropertyRepositoryInterface $propertyRepository): Response
    {
        return $this->render('@ZikulaProfile/Property/list.html.twig', [
            'properties' => $propertyRepository->findBy([], ['weight' => 'ASC']),
        ]);
    }

    #[Route('/edit/{id}', name: 'zikulaprofilebundle_property_edit', defaults: ['id' => null])]
    public function edit(Request $request, ManagerRegistry $doctrine, Property $propertyEntity = null): Response
    {
        if (!isset($propertyEntity)) {
            $propertyEntity = new Property();
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
    public function delete(Request $request, ManagerRegistry $doctrine, Property $propertyEntity): Response
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
