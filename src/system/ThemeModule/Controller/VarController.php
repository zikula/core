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

namespace Zikula\ThemeModule\Controller;

use InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Core\Controller\AbstractController;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ThemeModule\Engine\Annotation\Theme;

class VarController extends AbstractController
{
    /**
     * Configure a theme's variables based on provided .yml definitions for each field.
     *
     * @Route("/admin/var/{themeName}")
     * @Theme("admin")
     * @Template("@ZikulaThemeModule/Var/var.html.twig")
     *
     * @return array|RedirectResponse
     *
     * @throws InvalidArgumentException if theme type is not twig-based
     */
    public function varAction(
        Request $request,
        VariableApiInterface $variableApi,
        ZikulaHttpKernelInterface $kernel,
        string $themeName
    ) {
        $themeBundle = $kernel->getBundle($themeName);
        $themeVarsPath = $themeBundle->getConfigPath() . '/variables.yml';
        if (!file_exists($themeVarsPath)) {
            $this->addFlash('warning', $this->trans('%theme% has no configuration.', ['%theme%' => $themeName]));

            return $this->redirectToRoute('zikulathememodule_theme_view');
        }
        $variableDefinitions = Yaml::parse(file_get_contents($themeVarsPath));
        /** @var FormBuilder $formBuilder */
        $formBuilder = $this->createFormBuilder($themeBundle->getThemeVars());
        foreach ($variableDefinitions as $fieldName => $definitions) {
            $options = $definitions['options'] ?? [];
            if (isset($definitions['type'])) {
                $formBuilder->add($fieldName, $definitions['type'], $options);
            }
        }
        $formBuilder
            ->add('save', SubmitType::class, [
                'label' => $this->trans('Save'),
                'icon' => 'fa-check fa-lg',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('toDefault', SubmitType::class, [
                'label' => $this->trans('Set to defaults'),
                'icon' => 'fa-refresh fa-lg',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $this->trans('Cancel'),
                'icon' => 'fa-times fa-lg',
                'attr' => [
                    'class' => 'btn btn-danger'
                ]
            ])
        ;
        $form = $formBuilder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                // pseudo-hack to save theme vars in to modvars table
                $variableApi->setAll($themeName, $form->getData());
                $this->addFlash('status', $this->trans('Done! Theme configuration updated.'));
            } elseif ($form->get('toDefault')->isClicked()) {
                $variableApi->setAll($themeName, $themeBundle->getDefaultThemeVars());
                $this->addFlash('status', $this->trans('Done! Theme configuration updated to default values.'));
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->trans('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulathememodule_theme_view');
        }

        return [
            'themeName' => $themeName,
            'form' => $form->createView()
        ];
    }
}
