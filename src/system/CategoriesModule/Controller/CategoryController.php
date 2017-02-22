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
            $category = new CategoryEntity();
            $category->setDisplay_name($this->localize($category->getDisplay_name()));
            $category->setDisplay_desc($this->localize($category->getDisplay_desc()));
        }

        $form = $this->createForm('Zikula\CategoriesModule\Form\Type\CategoryType',
            $category, [
                'translator' => $this->get('translator.default')
            ]
        );

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();

                $this->addFlash('status', $this->__('Done!'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulacategoriesmodule_admin_view');
        }
        $templateParameters = [
            'locales' => $this->get('zikula_settings_module.locale_api')->getSupportedLocaleNames(),
            'form' => $form->createView()
        ];
        if ($request->isXmlHttpRequest()) {
            return $this->render('@ZikulaCategoriesModule/Category/edit_form.html.twig', $templateParameters, new PlainResponse());
        }

        return $templateParameters;
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

    private function localize(array $values)
    {
        $locales = $this->get('zikula_settings_module.locale_api')->getSupportedLocales();
        foreach ($locales as $code) {
            $values[$code] = isset($values[$code]) ? $values[$code] : '';
        }

        return $values;
    }
}
