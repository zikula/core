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

namespace Zikula\PermissionsModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

class FilterListType extends AbstractType
{
    use TranslatorTrait;

    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('filterGroup', ChoiceType::class, [
                'choices' => array_flip($options['groupChoices']),
                'attr' => [
                    'class' => 'form-control-sm'
                ]
            ])
            ->add('filterComponent', ChoiceType::class, [
                'choices' => $options['componentChoices'],
                'attr' => [
                    'class' => 'form-control-sm'
                ]
            ])
            ->add('reset', ButtonType::class, [
                'label' => $this->trans('Reset'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default btn-sm'
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulapermissionsmodule_filterlist';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => [
                'class' => 'form-inline'
            ],
            'groupChoices' => [],
            'componentChoices' => []
        ]);
    }
}
