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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Bundle\FormExtensionBundle\Validator\Constraints\ValidController;

/**
 * Controller form type.
 */
class ControllerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('controller', TextType::class, [
            'label' => 'Controller',
            'help' => 'FQCN::method, for example <code>Zikula\FooModule\Controller\BarController::mainAction</code>',
            'help_html' => true,
            'required' => $options['required'] ?? false,
            'constraints' => [
                new ValidController()
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
