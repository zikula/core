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

namespace Zikula\LegalBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('resetagreement', ChoiceType::class, [
                'label'             => 'Reset user group\'s acceptance of site policies',
                'choices'           => $options['groupChoices'],
                'required'          => false,
                'expanded'          => false,
                'multiple'          => false,
                'help'              => 'Leave blank to leave users unaffected.',
                'alert'             => ['Notice: This setting resets the acceptance of the site policies for all users in this group. Next time they want to log-in, they will have to acknowledge their acceptance of them again, and will not be able to log-in if they do not. This action does not affect the main administrator account. You can perform the same operation for individual users by visiting the Users manager in the site admin panel.' => 'info']
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'icon'  => 'fa-check',
                'attr'  => [
                    'class' => 'btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => 'Cancel',
                'icon'  => 'fa-times'
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'zikulalegalbundle_config';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'groupChoices' => [],
        ]);
    }
}
