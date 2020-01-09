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

namespace Zikula\UsersModule\Form\Type\RegistrationType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\UsersModule\Validator\Constraints\ValidUserFields;

class DefaultRegistrationType extends AbstractType
{
    use TranslatorTrait;

    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uname', HiddenType::class)
            ->add('email', HiddenType::class)
            ->add('submit', SubmitType::class, [
                'label' => $this->trans('Save'),
                'icon' => 'fa-check',
                'attr' => ['class' => 'btn btn-success']
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $this->trans('Cancel'),
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-danger']
            ])
            ->add('reset', SubmitType::class, [
                'label' => $this->translator->trans('Reset'),
                'icon' => 'fa-refresh',
                'attr' => ['class' => 'btn btn-primary']
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulausersmodule_defaultregistration';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'constraints' => [
                new ValidUserFields()
            ]
        ]);
    }
}
