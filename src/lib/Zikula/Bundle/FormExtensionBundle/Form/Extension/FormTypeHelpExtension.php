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

namespace Zikula\Bundle\FormExtensionBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Xatoo (http://stackoverflow.com/users/3492835/xatoo)
 * @see http://stackoverflow.com/q/27905939/2600812
 */
class FormTypeHelpExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAttribute('help', $options['help'])
            ->setAttribute('input_group', $options['input_group'])
            ->setAttribute('alert', $options['alert'])
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['help'] = $options['help'];
        $view->vars['input_group'] = $options['input_group'];
        $view->vars['alert'] = $options['alert'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'input_group' => null,
            'alert' => null
        ]);

        $resolver->setAllowedTypes('help', ['string', 'null', 'array']);
    }

    public function getExtendedTypes()
    {
        return [FormType::class]; // Extend all field types
    }
}
