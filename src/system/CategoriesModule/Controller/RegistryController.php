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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\CategoriesModule\Entity\CategoryRegistryEntity;
use Zikula\Core\Controller\AbstractController;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * @Route("/registry")
 *
 * Controller for handling category registries.
 */
class RegistryController extends AbstractController
{
    /**
     * @Route("/edit")
     * @Template
     * @Theme("admin")
     *
     * Creates or edits a category registry.
     *
     * @param Request $request
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to administrate the module
     */
    public function editAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        if ($request->isMethod('POST')) {
            $this->get('zikula_core.common.csrf_token_handler')->validate($request->request->get('csrfToken'));

            if (!$request->request->get('category_submit', null)) {
                // got here through selector auto-submit
                return $this->redirectToRoute('zikulacategoriesmodule_registry_edit', [
                    'category_registry' => $request->request->get('category_registry', null)
                ]);
            }

            // get data from post
            $data = $request->request->get('category_registry', null);

            // do some validation
            $valid = true;
            if (empty($data['modname'])) {
                $this->addFlash('error', $this->__('Error! You did not select a module.'));
                $valid = false;
            }
            if (empty($data['entityname'])) {
                $this->addFlash('error', $this->__('Error! You did not select an entity.'));
                $valid = false;
            }
            if (empty($data['property'])) {
                $this->addFlash('error', $this->__('Error! You did not enter a property name.'));
                $valid = false;
            }
            if ((int)$data['category_id'] == 0) {
                $this->addFlash('error', $this->__('Error! You did not select a category.'));
                $valid = false;
            }
            if (!$valid) {
                return $this->redirectToRoute('zikulacategoriesmodule_registry_edit');
            }

            $entityManager = $this->get('doctrine')->getManager();
            if (isset($data['id']) && (int)$data['id'] > 0) {
                // update existing registry
                $registry = $entityManager->find('ZikulaCategoriesModule:CategoryRegistryEntity', $data['id']);
                if (null === $registry) {
                    throw new NotFoundHttpException($this->__('Registry entry not found.'));
                }
            } else {
                // create new registry
                $registry = new CategoryRegistryEntity();
            }
            $registry->merge($data);
            $entityManager->persist($registry);
            $entityManager->flush();
            $this->addFlash('status', $this->__('Done! Saved the category registry entry.'));

            return $this->redirectToRoute('zikulacategoriesmodule_registry_edit');
        } else {
            $rootId = $request->query->get('dr', 1);
            $id = $request->query->get('id', 0);

            $newRegistry = new CategoryRegistryEntity();

            $category_registry = $request->query->get('category_registry', null);
            if ($category_registry) {
                $newRegistry->merge($category_registry);
                $newRegistry = $newRegistry->toArray();
            }

            $entityManager = $this->get('doctrine')->getManager();

            $registries = $entityManager->getRepository('ZikulaCategoriesModule:CategoryRegistryEntity')
                ->findBy([], ['modname' => 'ASC', 'property' => 'ASC']);
            $modules = $entityManager->getRepository('ZikulaExtensionsModule:ExtensionEntity')
                ->findBy(['state' => 3], ['displayname' => 'ASC']);

            $moduleOptions = [];
            foreach ($modules as $module) {
                $bundle = \ModUtil::getModule($module['name']);
                if (null !== $bundle && !class_exists($bundle->getVersionClass())) {
                    // this check just confirming a Core-2.0 spec bundle - remove in 2.0.0
                    // then instead of getting MetaData, could just do $capabilityApi->getCapabilitiesOf($module['name'])
                    $capabilities = $bundle->getMetaData()->getCapabilities();
                    if (!isset($capabilities['categorizable'])) {
                        continue; // skip this module if not categorizable
                    }
                }
                $moduleOptions[$module['name']] = $module['displayname'];
            }

            return [
                'registries' => $registries,
                'moduleOptions' => $moduleOptions,
                'newRegistry' => $newRegistry,
                'root_id' => $rootId,
                'id' => $id,
                'csrfToken' => $this->get('zikula_core.common.csrf_token_handler')->generate()
            ];
        }
    }

    /**
     * @Route("/delete")
     * @Template
     * @Theme("admin")
     *
     * Deletes a category registry.
     *
     * @param Request $request
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to administrate the module
     */
    public function deleteAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $id = $request->query->get('id', 0);

        $entityManager = $this->get('doctrine')->getManager();
        $registry = $entityManager->find('ZikulaCategoriesModule:CategoryRegistryEntity', $id);
        if (null === $registry) {
            throw new NotFoundHttpException($this->__('Registry entry not found.'));
        }

        $form = $this->createFormBuilder()
            ->add('delete', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->__('Delete'),
                'icon' => 'fa-trash-o',
                'attr' => [
                    'class' => 'btn btn-danger'
                ]
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ])
            ->getForm();

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $entityManager->remove($registry);
                $entityManager->flush();
                $this->addFlash('status', $this->__('Done! Registry entry deleted.'));
            }
            if ($form->get('cancel')->isClicked()) {
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
