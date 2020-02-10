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

namespace Zikula\Bundle\FormExtensionBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Translation\Extractor\Annotation\Ignore;

/**
 * Controller form type.
 */
class ControllerType extends AbstractType
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var array
     */
    private $controllerChoices = [];

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (1 > count($this->controllerChoices)) {
            foreach ($this->router->getRouteCollection()->all() as $route => $params) {
                $defaults = $params->getDefaults();
                if (!isset($defaults['_controller']) || empty($defaults['_controller'])) {
                    // skip routes without controller
                    continue;
                }
                $controller = $defaults['_controller'] ?? '';
                $optionLabel = str_pad($route . ' ', 80, '-') . '> ' . $controller;
                $optionValue = $route . '###' . $controller;
                $this->controllerChoices[$optionLabel] = $optionValue;
            }
            ksort($this->controllerChoices);
        }

        $builder->add('controller', ChoiceType::class, [
            'label' => 'Controller',
            'choices' => /** @Ignore */$this->controllerChoices,
            'required' => $options['required'] ?? false,
            'attr' => [
                'style' => 'font-family: monospace; font-size: 10px'
            ]
        ]);
        if (in_array('query', $options['parameterTypes'])) {
            $builder->add('query', TextType::class, [
                'label' => 'GET parameters',
                'help' => 'Separate with &, for example: <code>foo=2&bar=5</code>',
                'help_html' => true,
                'required' => false
            ]);
        }
        if (in_array('request', $options['parameterTypes'])) {
            $builder->add('request', TextType::class, [
                'label' => 'POST parameters',
                'help' => 'Separate with &, for example: <code>foo=2&bar=5</code>',
                'help_html' => true,
                'required' => false
            ]);
        }
        if (in_array('attributes', $options['parameterTypes'])) {
            $builder->add('attributes', TextType::class, [
                'label' => 'Request attributes',
                'help' => 'Separate with &, for example: <code>foo=2&bar=5</code>',
                'help_html' => true,
                'required' => false
            ]);
        }
    }

    public function getBlockPrefix()
    {
        return 'zikula_controller';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'parameterTypes' => ['query', 'request', 'attributes']
        ]);
    }
}
