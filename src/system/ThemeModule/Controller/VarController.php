<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\Request;
use Zikula\Core\Controller\AbstractController;
use Zikula\ThemeModule\Engine\Annotation\Theme;

class VarController extends AbstractController
{
    /**
     * Configure a theme's variables based on provided .yml definitions for each field.
     * @Route("/admin/var/{themeName}")
     * @todo change route name to /admin/variable/{themeName} when similar named is removed?
     * @Theme("admin")
     * @Template
     *
     * @param Request $request
     * @param string $themeName
     * @return mixed
     * @throws \InvalidArgumentException if theme type is not twig-based
     */
    public function varAction(Request $request, $themeName)
    {
        $themeBundle = $this->get('kernel')->getBundle($themeName);
        $themeVarsPath = $themeBundle->getConfigPath() . '/variables.yml';
        if (!file_exists($themeVarsPath)) {
            $this->addFlash('warning', $this->__f('%theme% has no configuration.', ['%theme%' => $themeName]));

            return $this->redirectToRoute('zikulathememodule_theme_view');
        }
        $variableDefinitions = Yaml::parse(file_get_contents($themeVarsPath));
        /** @var \Symfony\Component\Form\FormBuilder $formBuilder */
        $formBuilder = $this->createFormBuilder($themeBundle->getThemeVars());
        foreach ($variableDefinitions as $fieldName => $definitions) {
            $options = isset($definitions['options']) ? $definitions['options'] : [];
            if (isset($definitions['type'])) {
                $formBuilder->add($fieldName, $definitions['type'], $options);
            }
        }
        $formBuilder
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->__('Save'),
                'icon' => 'fa-check fa-lg',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('toDefault', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->__('Set to defaults'),
                'icon' => 'fa-refresh fa-lg',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->__('Cancel'),
                'icon' => 'fa-times fa-lg',
                'attr' => [
                    'class' => 'btn btn-danger'
                ]
            ])
        ;
        $form = $formBuilder->getForm();

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                // pseudo-hack to save theme vars in to modvars table
                $this->get('zikula_extensions_module.api.variable')->setAll($themeName, $form->getData());
                $this->addFlash('status', $this->__('Done! Theme configuration updated.'));
            } elseif ($form->get('toDefault')->isClicked()) {
                $this->get('zikula_extensions_module.api.variable')->setAll($themeName, $themeBundle->getDefaultThemeVars());
                $this->addFlash('status', $this->__('Done! Theme configuration updated to default values.'));
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulathememodule_theme_view');
        }

        return [
            'themeName' => $themeName,
            'form' => $form->createView()
        ];
    }
}
