<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeleteType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('users', EntityType::class, [
                'choice_attr' => function () {
                    return ['class' => 'user-checkboxes'];
                },
                'class' => 'ZikulaUsersModule:UserEntity',
                'choices' => $options['choices'],
                'expanded' => true,
                'multiple' => true,
                'choice_label' => 'uname'
            ])
            ->add('delete', SubmitType::class, [
                'label' => $options['translator']->__('Delete selected users'),
                'icon' => 'fa-trash-o',
                'attr' => [
                    'class' => 'btn btn-danger'
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulausersmodule_delete';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
            'choices' => [],
            'attr' => ['id' => 'users_searchresults']
        ]);
    }
}
