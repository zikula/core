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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Response\PlainResponse;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * @Route("/admin/category")
 *
 * Controller for handling category registries.
 */
class CategoryController extends AbstractController
{
    /**
     * @Route("/edit/{category}", requirements={"category" = "^[1-9]\d*$"}, options={"expose"=true})
     * @Template
     * @Theme("admin")
     *
     * Creates or edits a category.
     *
     * @param Request $request
     *
     * @param CategoryEntity $category
     * @return array|Response|PlainResponse
     */
    public function editAction(Request $request, CategoryEntity $category = null)
    {
        if (null === $category) {
            $category = new CategoryEntity($this->get('zikula_settings_module.locale_api')->getSupportedLocales());
        }

        $form = $this->createForm('Zikula\CategoriesModule\Form\Type\CategoryType',
            $category, [
                'translator' => $this->get('translator.default'),
                'locales' => $this->get('zikula_settings_module.locale_api')->getSupportedLocales(),
            ]
        );

        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->get('save')->isClicked() && $form->isValid()) {
                $category = $form->getData();
                $category->setPath($category->getParent()->getPath() . '/' . $category->getName());
                $category->setIPath($category->getParent()->getIPath() . '/' . $category->getId());
                $this->updateChildPaths($category);
                $this->get('doctrine')->getManager()->flush();
                $this->addFlash('status', $this->__('Done!'));

                return $this->redirectToRoute('zikulacategoriesmodule_admin_view');
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));

                return $this->redirectToRoute('zikulacategoriesmodule_admin_view');
            }
        }

        return [
            'locales' => $this->get('zikula_settings_module.locale_api')->getSupportedLocaleNames(),
            'form' => $form->createView()
        ];
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
     * @return array|Response
     *
     * @throws AccessDeniedException Thrown if the user doesn't have permission to administrate the module
     */
    public function deleteAction(Request $request)
    {
        // nada
    }

    /**
     * Recursive method to update all child paths to reflect updated parent path
     * @param CategoryEntity $category
     */
    private function updateChildPaths(CategoryEntity $category)
    {
        /** @var CategoryEntity[] $children */
        $children = $category->getChildren();
        foreach ($children as $child) {
            // if path of child does not include full path of recently updated parent,
            // then parent has changed and child path must be updated
            if (0 !== strpos($child->getPath(), $category->getPath() . '/')) {
                $child->setPath($category->getPath() . '/' . $child->getName());
                if ($child->getChildren()->count() > 0) {
                    $this->updateChildPaths($child);
                }
            }
        }
    }
}
