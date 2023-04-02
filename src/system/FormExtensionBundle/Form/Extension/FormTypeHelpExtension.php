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

namespace Zikula\FormExtensionBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

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
            'alert' => null,
        ]);

        $resolver->setAllowedTypes('help', ['string', 'null', 'array', TranslatableMessage::class]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class]; // Extend all field types
    }
}
